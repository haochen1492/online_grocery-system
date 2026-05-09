<?php
include '../includes/dbconnect.php';
session_start();

$featured_query = "SELECT * FROM products LIMIT 4";
$featured_result = $conn->query($featured_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Infinity Grocer - Home</title>
    <link rel="stylesheet" href="includes/styles.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        
        /* Fixed Banner Height & Centering */
        .slideshow-container {
            width: 100%;
            height: 450px;
            position: relative;
            overflow: hidden;
            background: #eee;
        }
        .mySlides img {
            width: 100%;
            height: 450px;
            object-fit: cover; /* Stops images from stretching */
        }

        /* Product Grid Styling */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 40px 5%;
        }
        .card {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 180px; object-fit: contain; margin-bottom: 15px; }
        .btn {
            display: inline-block;
            background: #329b18;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header>
    <?php include 'includes/header.php'; ?>
</header>

<div class="slideshow-container">
    <div class="mySlides fade"><img src="images/banner_1.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_2.webp"></div>
    <div class="mySlides fade"><img src="images/banner_3.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_4.jpg"></div>
    <div class="mySlides fade"><img src="images/banner_5.webp"></div>

    <a class="prev" onclick="plusSlides(-1)" style="position:absolute; top:50%; left:20px; cursor:pointer; color:white; font-size:30px; background:rgba(0,0,0,0.3); padding:10px;">&#10094;</a>
    <a class="next" onclick="plusSlides(1)" style="position:absolute; top:50%; right:20px; cursor:pointer; color:white; font-size:30px; background:rgba(0,0,0,0.3); padding:10px;">&#10095;</a>
</div>

<h2 style="text-align:center; margin-top:40px; color: #333;">Featured Products</h2>

<div class="product-grid">
    <?php if ($featured_result->num_rows > 0): ?>
        <?php while($row = $featured_result->fetch_assoc()): ?>
            <div class="card">
                <img src="images/<?php echo $row['product_image']; ?>" alt="<?php echo $row['name']; ?>">
                <h4><?php echo $row['name']; ?></h4>
                <p style="color: #329b18; font-weight: bold; font-size: 1.2em;">RM <?php echo number_format($row['price'], 2); ?></p>
                <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn">View Items</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; grid-column: 1/-1;">No products found in database.</p>
    <?php endif; ?>
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
    setInterval(() => { plusSlides(1); }, 5000); // Auto-slide
</script>
</body>
</html>