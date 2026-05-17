<?php
include '../includes/dbconnect.php';
session_start();

// Fetch 4 products for the 'Featured' section
$featured_query = "SELECT * FROM products LIMIT 4";
$featured_result = $conn->query($featured_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infinity Grocer - Home</title>
    <link rel="stylesheet" href="includes/styles.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }


        .slideshow-container {
            width: 100%;
            height: 450px;
            position: relative;
            overflow: hidden;
            background-color: #f0f0f0;
        }
        .mySlides img {
            width: 100%;
            height: 450px;
            object-fit: cover;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .product-card { 
            width: 230px;
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
        .product-price { 
            color: #329b18; 
            font-weight: bold; 
            font-size: 1.2em; 
        }
        
        .stock-label { font-size: 0.85em; color: #777; margin-bottom: 15px; }
        .btn {
            display: inline-block;
            background: #329b18;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="slideshow-container">
    <div class="mySlides fade"><img src="images/banner_2.webp"></div>
    <div class="mySlides fade"><img src="images/banner_1.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_3.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_4.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_5.webp"></div>

    <a class="prev" onclick="plusSlides(-1)" style="position:absolute; top:45%; left:10px; cursor:pointer; font-size:30px; color:white; background:rgba(0,0,0,0.5); padding:10px;">&#10094;</a>
    <a class="next" onclick="plusSlides(1)" style="position:absolute; top:45%; right:10px; cursor:pointer; font-size:30px; color:white; background:rgba(0,0,0,0.5); padding:10px;">&#10095;</a>
</div>

<h2 style="text-align:center; margin-top:30px;">Featured Products</h2>
<div class="product-grid">
    <?php while($row = $featured_result->fetch_assoc()): ?>
        <div class="product-card" onclick="window.location.href='product_detail.php?product_id=<?php echo $row['product_id']; ?>'">
            <img src="../admin/products/<?php echo $row['product_image']; ?>" style="width:100%; height:150px; object-fit:contain;">
            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
            <p>RM <?php echo number_format($row['price'], 2); ?></p>
            <!--<a href="product_details.php?product_id=<?php echo $row['product_id']; ?>" class="btn">View Items</a>-->
        </div>
    <?php endwhile; ?>
</div>

<script>
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) { showSlides(slideIndex += n); }
function showSlides(n) {
    let slides = document.getElementsByClassName("mySlides");
    if (n > slides.length) {slideIndex = 1}    
    if (n < 1) {slideIndex = slides.length}
    for (let i = 0; i < slides.length; i++) { slides[i].style.display = "none"; }
    slides[slideIndex-1].style.display = "block";  
}
// Auto slide every 5 seconds
setInterval(() => { plusSlides(1); }, 5000);

</script>
</body>
</html>