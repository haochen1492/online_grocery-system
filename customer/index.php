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
        
        /* Forces the header into a row */
        .header-container nav { display: flex !important; flex-direction: row !important; gap: 20px; align-items: center; }

        /* Slideshow Fix */
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
            object-fit: cover; /* Prevents stretching */
        }

        /* Product Grid Fix */
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .card {
            width: 250px;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
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
    <div class="mySlides fade"><img src="images/banner_1.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_2.webp"></div>
    <div class="mySlides fade"><img src="images/banner_3.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_4.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_5.webp"></div>

    <a class="prev" onclick="plusSlides(-1)" style="position:absolute; top:45%; left:10px; cursor:pointer; font-size:30px; color:white; background:rgba(0,0,0,0.5); padding:10px;">&#10094;</a>
    <a class="next" onclick="plusSlides(1)" style="position:absolute; top:45%; right:10px; cursor:pointer; font-size:30px; color:white; background:rgba(0,0,0,0.5); padding:10px;">&#10095;</a>
</div>

<h2 style="text-align:center; margin-top:30px;">Featured Products</h2>
<div class="product-grid">
    <?php while($row = $featured_result->fetch_assoc()): ?>
        <div class="card">
            <img src="images/<?php echo $row['product_image']; ?>" style="width:100%; height:150px; object-fit:contain;">
            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
            <p>RM <?php echo number_format($row['price'], 2); ?></p>
            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn">View Items</a>
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