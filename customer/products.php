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

// build product query based on filters
if ($category_id && !empty($search_query)) {
    // Both Category AND Search used (e.g., searching "apple" inside "Fruits")
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND active = ? AND name LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("iis", $category_id, $active, $search_param);
    
} elseif ($category_id) {
    // Only Category clicked
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND active = ?");
    $stmt->bind_param("ii", $category_id, $active);
    
} elseif (!empty($search_query)) {
    // Only Search typed
    $stmt = $conn->prepare("SELECT * FROM products WHERE active = ? AND name LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("is", $active, $search_param);
    
} else {
    // Default: Show all active products
    $stmt = $conn->prepare("SELECT * FROM products WHERE active = ?");
    $stmt->bind_param("i", $active);
}

$stmt->execute();
$product_result = $stmt->get_result();
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
                    <div class="stock-label">Availability: <?php echo $row['stock_quantity']; ?> in stock</div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1;">No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

</body>
</html>