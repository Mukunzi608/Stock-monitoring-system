<?php include("connect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Dashboard</title>
<!-- Auto-refresh every 5 seconds -->
<meta http-equiv="refresh" content="3">
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background-color: #f4f4f4;
    }

    /* Navbar */
    nav {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    nav a {
        color: white;
        text-decoration: none;
        margin-left: 15px;
        font-weight: bold;
    }

    nav a:hover {
        text-decoration: underline;
    }

    /* Container */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 10px;
    }

    h2 {
        margin-bottom: 20px;
        color: #333;
    }

    /* Product cards */
    .product-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .product-card {
        background: white;
        width: 200px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
        padding-bottom: 10px;
    }

    .product-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .product-card h3 {
        font-size: 1.1em;
        margin: 10px 0 5px 0;
    }

    .product-card p {
        margin: 5px 0;
        color: #555;
        font-size: 0.9em;
    }

</style>
</head>
<body>

<nav>
    <div class="brand">Stock System</div>
</nav>

<div class="container">
    <h2>Stock Overview</h2>
    <div class="product-grid">
        <?php
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='product-card'>
                    <img src='uploads/".$row['image']."' alt='".$row['name']."'>
                    <h3>".$row['name']."</h3>
                    <p>Quantity: ".$row['quantity']."</p>
                    <p>Price: $".$row['price']."</p>
                    <p>Quality: ".$row['quality']."</p>
                </div>";
            }
        } else {
            echo "<p>No products found</p>";
        }
        $conn->close();
        ?>
    </div>
</div>

</body>
</html>