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
    .slideshow-container {
        max-width: 100%;
        position: relative;
        margin: auto;
        height: 450px; /* Adjust this height to fit your design */
        overflow: hidden;
    }

    .mySlides img {
        width: 100%;
        height: 450px; 
        object-fit: cover; /* This is critical: it crops the image to fit without stretching */
    }

    /* Animation for smooth transition */
    .fade {
        animation-name: fade;
        animation-duration: 1.5s;
    }

    @keyframes fade {
        from {opacity: .4} 
        to {opacity: 1}
    }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="slideshow-container">
    <div class="mySlides fade">
        <img src="image/banner_1.jpg" style="width:100%">
    </div>

    <div class="mySlides fade">
        <img src="image/banner_6.jpg" style="width:100%">
    </div>

    <div class="mySlides fade">
        <img src="image/banner_3.jpg" style="width:100%">
    </div>

    <div class="mySlides fade">
        <img src="image/banner_4.jpg" style="width:100%">
    </div>

    <div class="mySlides fade">
        <img src="image/banner_5.webp" style="width:100%">
    </div>

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