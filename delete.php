<?php
include("connect.php");

$selected_product = null;
$error = '';

// Step 1: Select product
if(isset($_POST['select_product'])){
    $id = $_POST['product_id'];
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if($result && $result->num_rows > 0){
        $selected_product = $result->fetch_assoc();
    } else {
        $error = "Product not found.";
    }
}

// Step 2: Delete product safely
if(isset($_POST['delete_product'])){
    $id = $_POST['id'];

    // Fetch product first
    $sql = "SELECT image FROM products WHERE id = $id";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        $product = $result->fetch_assoc();
        $image_file = $product['image'];
        $upload_dir = "uploads/";

        // Delete from database
        if($conn->query("DELETE FROM products WHERE id = $id")){
            // Delete image file safely
            if(!empty($image_file) && file_exists($upload_dir.$image_file) && is_file($upload_dir.$image_file)){
                unlink($upload_dir.$image_file);
            }

            // Redirect to avoid resubmission on refresh
            header("Location: delete.php");
            exit;
        } else {
            $error = "Database error: ".$conn->error;
        }
    } else {
        $error = "Product not found.";
    }
}

// Fetch all products for dropdown
$products = $conn->query("SELECT * FROM products");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delete Product</title>
<style>
body { font-family: Arial; background:#f4f4f4; margin:0; }
.container { max-width:600px; margin:30px auto; background:white; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2 { margin-bottom:20px; color:#333;}
select { width:100%; padding:10px; margin-bottom:15px; border-radius:4px; border:1px solid #ccc;}
input[type=submit] { background-color:#dc3545; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer;}
input[type=submit]:hover { background-color:#a71d2a; }
.message { margin-bottom:15px; padding:10px; border-radius:4px; }
.error { background-color:#f8d7da; color:#721c24; }
img { max-width:150px; display:block; margin-bottom:10px; border-radius:4px; }
</style>
</head>
<body>

<div class="container">
    <h2>Delete Product</h2>

    <?php if(!empty($error)) echo "<div class='message error'>$error</div>"; ?>

    <!-- Step 1: Select product -->
    <?php if(!$selected_product): ?>
    <form method="post">
        <label>Select Product to Delete:</label>
        <select name="product_id" required>
            <option value="">-- Choose Product --</option>
            <?php while($row = $products->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" name="select_product" value="Select Product">
    </form>
    <?php endif; ?>

    <!-- Step 2: Confirm delete -->
    <?php if($selected_product): ?>
    <h3><?php echo htmlspecialchars($selected_product['name']); ?></h3>
    <p>Quantity: <?php echo $selected_product['quantity']; ?></p>
    <p>Price: $<?php echo $selected_product['price']; ?></p>
    <p>Quality: <?php echo htmlspecialchars($selected_product['quality']); ?></p>
    <img src="uploads/<?php echo $selected_product['image']; ?>" alt="<?php echo $selected_product['name']; ?>">

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $selected_product['id']; ?>">
        <input type="submit" name="delete_product" value="Confirm Delete">
    </form>
    <?php endif; ?>
</div>

</body>
</html>