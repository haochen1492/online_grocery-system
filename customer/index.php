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
        /* Slideshow Container */
        .slideshow-container { max-width: 1000px; position: relative; margin: auto; height: 400px; overflow: hidden; }
        .mySlides { display: none; height: 100%; }
        .mySlides img { width: 100%; height: 100%; object-fit: cover; }
        .prev, .next { cursor: pointer; position: absolute; top: 50%; padding: 16px; color: white; font-weight: bold; background: rgba(0,0,0,0.3); border-radius: 3px; }
        .next { right: 0; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; text-align: center; border-radius: 8px; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="slideshow-container">
    <div class="mySlides fade"><img src="images/banner1.jpg"></div>
    <div class="mySlides fade"><img src="images/banner2.jpg"></div>
    <div class="mySlides fade"><img src="images/banner3.jpg"></div>
    <div class="mySlides fade"><img src="images/banner4.jpg"></div>

    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>
</div>

<h2 style="text-align:center; margin-top:30px;">Featured Products</h2>
<div class="product-grid">
    <?php while($row = $featured_result->fetch_assoc()): ?>
        <div class="card">
            <img src="images/<?php echo $row['product_image']; ?>" style="width:100%; height:150px; object-fit:contain;">
            <h4><?php echo $row['name']; ?></h4>
            <p>RM <?php echo number_format($row['price'], 2); ?></p>
            <a href="product.php" class="btn">View Items</a>
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
// Auto change every 5 seconds
setInterval(() => { plusSlides(1); }, 5000);
</script>
</body>
</html>