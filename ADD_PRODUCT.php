<?php
include("connect.php");

// Handle form submission
if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $quality = $_POST['quality'];

    // Handle file upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allow only certain file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array($file_ext, $allowed)){
            // Rename file to avoid conflicts
            $new_file_name = time().'_'.$file_name;
            $upload_dir = "uploads/";
            if(move_uploaded_file($file_tmp, $upload_dir.$new_file_name)){
                // Insert into database
                $sql = "INSERT INTO products (name, quantity, price, quality, image) 
                        VALUES ('$name', '$quantity', '$price', '$quality', '$new_file_name')";
                if($conn->query($sql) === TRUE){
                    $success = "Product added successfully!";
                } else {
                    $error = "Database error: ".$conn->error;
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    } else {
        $error = "Please upload a product image.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
    nav { background: #007bff; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
    nav a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
    nav a:hover { text-decoration: underline; }

    .container { max-width: 600px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 20px; color: #333; }
    input[type=text], input[type=number], input[type=file] {
        width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ccc;
    }
    input[type=submit] {
        background-color: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;
    }
    input[type=submit]:hover { background-color: #0056b3; }

    .message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
</style>
</head>
<body>

<nav>
    <div class="brand">Stock System</div>
    <!-- Buttons removed -->
</nav>

<div class="container">
    <h2>Add Product</h2>

    <?php
    if(isset($success)) echo "<div class='message success'>$success</div>";
    if(isset($error)) echo "<div class='message error'>$error</div>";
    ?>

    <form method="post" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <label>Price</label>
        <input type="number" name="price" step="0.01" required>

        <label>Quality</label>
        <input type="text" name="quality" required>

        <label>Product Image</label>
        <input type="file" name="image" accept="image/*" required>

        <input type="submit" name="submit" value="Add Product">
    </form>
</div>

</body>
</html>