<?php
include("connect.php");

$selected_product = null;
$error = '';

// Step 1: Handle product selection
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

// Step 2: Handle product update safely
if(isset($_POST['update_product'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $quality = $_POST['quality'];

    // Fetch current product from DB
    $sql = "SELECT image FROM products WHERE id = $id";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        $product = $result->fetch_assoc();
        $image_name = $product['image']; // current image

        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];

            if(in_array($file_ext, $allowed)){
                $upload_dir = "uploads/";
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $new_file_name = time().'_'.$file_name;

                if(move_uploaded_file($file_tmp, $upload_dir.$new_file_name)){
                    // Delete old image safely
                    if(!empty($image_name) && file_exists($upload_dir.$image_name) && is_file($upload_dir.$image_name)){
                        unlink($upload_dir.$image_name);
                    }
                    $image_name = $new_file_name;
                } else {
                    $error = "Failed to upload new image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
            }
        }

        // Update database
        if(empty($error)){
            $update_sql = "UPDATE products SET 
                            name='$name',
                            quantity='$quantity',
                            price='$price',
                            quality='$quality',
                            image='$image_name'
                           WHERE id=$id";
            if($conn->query($update_sql) === TRUE){
                // Refresh product data
                header("Location: update.php?edited=$id"); // Redirect to prevent refresh issues
                exit;
            } else {
                $error = "Database error: ".$conn->error;
            }
        }
        // Re-fetch product if needed
        $selected_product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Product not found.";
    }
}

// Fetch all products for selection
$products = $conn->query("SELECT * FROM products");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Product</title>
<style>
body { font-family: Arial; background:#f4f4f4; margin:0; }
.container { max-width:600px; margin:30px auto; background:white; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2 { margin-bottom:20px; color:#333;}
select, input[type=text], input[type=number], input[type=file] { width:100%; padding:10px; margin-bottom:15px; border-radius:4px; border:1px solid #ccc;}
input[type=submit] { background-color:#007bff; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer;}
input[type=submit]:hover { background-color:#0056b3; }
.message { margin-bottom:15px; padding:10px; border-radius:4px; }
.error { background-color:#f8d7da; color:#721c24; }
img { max-width:150px; display:block; margin-bottom:10px; border-radius:4px; }
</style>
</head>
<body>

<div class="container">
    <h2>Update Product</h2>

    <?php if(!empty($error)) echo "<div class='message error'>$error</div>"; ?>

    <!-- Step 1: Select product -->
    <?php if(!$selected_product): ?>
    <form method="post">
        <label>Select Product to Edit:</label>
        <select name="product_id" required>
            <option value="">-- Choose Product --</option>
            <?php while($row = $products->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" name="select_product" value="Edit Product">
    </form>
    <?php endif; ?>

    <!-- Step 2: Edit form -->
    <?php if($selected_product): ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $selected_product['id']; ?>">

        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($selected_product['name']); ?>" required>

        <label>Quantity</label>
        <input type="number" name="quantity" value="<?php echo $selected_product['quantity']; ?>" required>

        <label>Price</label>
        <input type="number" name="price" step="0.01" value="<?php echo $selected_product['price']; ?>" required>

        <label>Quality</label>
        <input type="text" name="quality" value="<?php echo htmlspecialchars($selected_product['quality']); ?>" required>

        <label>Current Image</label>
        <img src="uploads/<?php echo $selected_product['image']; ?>" alt="<?php echo $selected_product['name']; ?>">

        <label>Change Image (optional)</label>
        <input type="file" name="image" accept="image/*">

        <input type="submit" name="update_product" value="Update Product">
    </form>
    <?php endif; ?>

</div>
</body>
</html>