<?php
include '../includes/dbconnect.php';
session_start();

// fetch all categories for the filter menu
$cat_query = "SELECT * FROM categories";
$cat_result = $conn->query($cat_query);

// get filter parameters
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$search_query = isset($_GET['search']) ? $_GET['search'] : null;
$active = 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit =16;
$offset = ($page - 1) * $limit;

// build product query based on filters
if ($category_id && !empty($search_query)) {
    // Both Category AND Search used (e.g., searching "apple" inside "Fruits")
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND active = ? AND name LIKE ? LIMIT ? OFFSET ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("iisii", $category_id, $active, $search_param, $limit, $offset);
    
} elseif ($category_id) {
    // Only Category clicked
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND active = ? LIMIT ? OFFSET ?");
    $stmt->bind_param("iiii", $category_id, $active, $limit, $offset);
    
} elseif (!empty($search_query)) {
    // Only Search typed
    $stmt = $conn->prepare("SELECT * FROM products WHERE active = ? AND name LIKE ? LIMIT ? OFFSET ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("isii", $active, $search_param, $limit, $offset);
    
} else {
    // Default: Show all active products
    $stmt = $conn->prepare("SELECT * FROM products WHERE active = ? LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $active, $limit, $offset);
}

// check stock quantity > 0 to show available stock or "Out of Stock"
$stock_query = "SELECT product_id, stock_quantity FROM products WHERE active = 1";
$stock_result = $conn->query($stock_query);
$stock_levels = [];
while ($row = $stock_result->fetch_assoc()) {
    $stock_levels[$row['product_id']] = $row['stock_quantity'];
}

$stmt->execute();
$product_result = $stmt->get_result();
$stmt->free_result();

//calculate total pages for pagination
if ($category_id && !empty($search_query)) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ? AND active = ? AND name LIKE ?");
    $count_stmt->bind_param("iis", $category_id, $active, $search_param);
} elseif ($category_id) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ? AND active = ?");
    $count_stmt->bind_param("ii", $category_id, $active);
} elseif (!empty($search_query)) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE active = ? AND name LIKE ?");
    $count_stmt->bind_param("is", $active, $search_param);
} else {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE active = ?");
    $count_stmt->bind_param("i", $active);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_pages = ceil($count_result['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
    <style>
        .product-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .filter-menu { margin-bottom: 30px; text-align: center; }
        .filter-menu a { 
            text-decoration: none; 
            padding: 8px 15px; 
            margin: 5px; 
            border: 1px solid #329b18; 
            color: #329b18; 
            border-radius: 20px;
            display: inline-block;
        }
        .filter-menu a:hover, .filter-menu a.active { background: #329b18; color: white; }
        
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); 
            gap: 25px; 
        }
        .product-card { 
            border: 1px solid #eee; 
            border-radius: 10px; 
            padding: 15px; 
            text-align: center; 
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .product-card:hover{
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .product-card img { 
            width: 100%; 
            height: 180px; 
            object-fit: contain; 
            margin-bottom: 15px; 
        }
        .product-name { 
            font-size: 1.1em; 
            font-weight: bold; 
            margin: 10px 0; 
        }
        .product-price { color: #329b18; font-weight: bold; font-size: 1.2em; }
        .stock-label { font-size: 0.85em; color: #777; margin-bottom: 15px; }
        .stock-label.out-of-stock { color: #c00; font-weight: bold; font-size: 15px; }
        .btn-cart { 
            display: block; 
            background: #329b18; 
            color: white; 
            padding: 10px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold;
        }
        .btn-cart:hover { background: #287a13; }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 40px 0;
        }

        .pagination a {
            text-decoration: none;
            padding: 10px 18px;
            border: 1px solid #329b18;
            color: #329b18;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .pagination a:hover {
            background-color: #329b18;
            color: white;
        }

        .pagination span {
            padding: 10px 15px;
            color: #555;
            font-weight: bold;
        }
        .pagination a.active {
        background-color: #329b18;
        color: white;
        border-color: #329b18;
    }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="product-container">
    <h2>Our Fresh Groceries</h2>

    <!-- category filter menu -->
    <div class="filter-menu">
        <a href="products.php" class="<?php echo !$category_id ? 'active' : ''; ?>">All Categories</a>
        <?php while($cat = $cat_result->fetch_assoc()): ?>
            <a href="products.php?category_id=<?php echo $cat['category_id']; ?>" 
               class="<?php echo $category_id == $cat['category_id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- product grid -->
    <div class="product-grid">
        <?php if ($product_result->num_rows > 0): ?>
            <?php while($row = $product_result->fetch_assoc()): ?>
                <div class="product-card" onclick="window.location.href='product_detail.php?product_id=<?php echo $row['product_id']; ?>'">
                    <?php 
                        // Path to product images folder
                        $img = !empty($row['product_image']) ? '../admin/products/'.$row['product_image'] : 'images/no-image.png';
                    ?>
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    
                    <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
                    <div class="stock-label">
                        <?php 
                            $stock_qty = isset($stock_levels[$row['product_id']]) ? $stock_levels[$row['product_id']] : 0;
                            echo $stock_qty > 0 ? 'Available: ' . htmlspecialchars($stock_qty) . ' in stock' : '<div class="stock-label out-of-stock">Out of Stock</div>';
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1;">No products found in this category.</p>
        <?php endif; ?>
    </div>
    <div class="pagination">
        <?php 
        // Build query string helper to maintain filters
        $query_string = "";
        if($category_id) $query_string .= "&category_id=" . $category_id;
        if($search_query) $query_string .= "&search=" . urlencode($search_query);
        ?>

        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>">« Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>" 
            class="<?php echo ($i == $page) ? 'active' : ''; ?>">
            <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>">Next »</a>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

</body>
</html>