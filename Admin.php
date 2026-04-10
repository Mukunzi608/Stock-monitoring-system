<?php
include("connect.php");

// Initialize messages
$error = '';
$success = '';

// --- Handle Add Product ---
if(isset($_POST['add_product'])){
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $quality = $_POST['quality'];

    // Check if product already exists
    $check = $conn->query("SELECT * FROM products WHERE name='$name' AND price='$price' AND quantity='$quantity'");
    if($check->num_rows > 0){
        $error = "Product already exists!";
    } else {
        $image_name = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if(in_array($file_ext, $allowed)){
                $upload_dir = "uploads/";
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $image_name = time().'_'.$file_name;
                move_uploaded_file($file_tmp, $upload_dir.$image_name);
            } else {
                $error = "Invalid image type.";
            }
        }

        if(empty($error)){
            if($conn->query("INSERT INTO products (name, quantity, price, quality, image) VALUES ('$name','$quantity','$price','$quality','$image_name')")){
                $success = "Product added successfully!";
                header("Location: admin.php"); // Redirect to refresh dashboard
                exit;
            } else $error = $conn->error;
        }
    }
}

// --- Handle Update Product ---
if(isset($_POST['update_product'])){
    $id = $_POST['id'];
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    if($res->num_rows == 0){
        $error = "Product not found!";
    } else {
        $product = $res->fetch_assoc();
        $name = $_POST['name'] ?: $product['name'];
        $quantity = $_POST['quantity'] ?: $product['quantity'];
        $price = $_POST['price'] ?: $product['price'];
        $quality = $_POST['quality'] ?: $product['quality'];
        $image_name = $product['image'];

        if(isset($_FILES['image']) && $_FILES['image']['error']==0){
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if(in_array($file_ext, $allowed)){
                $upload_dir = "uploads/";
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $new_file = time().'_'.$file_name;
                if(move_uploaded_file($file_tmp, $upload_dir.$new_file)){
                    if(!empty($image_name) && file_exists($upload_dir.$image_name)){
                        unlink($upload_dir.$image_name);
                    }
                    $image_name = $new_file;
                }
            } else $error = "Invalid image type.";
        }

        if(empty($error)){
            if($conn->query("UPDATE products SET name='$name', quantity='$quantity', price='$price', quality='$quality', image='$image_name' WHERE id=$id")){
                $success = "Product updated successfully!";
                header("Location: admin.php");
                exit;
            } else $error = $conn->error;
        }
    }
}

// --- Handle Delete Product ---
if(isset($_POST['delete_product'])){
    $id = $_POST['id'];
    $res = $conn->query("SELECT image FROM products WHERE id=$id");
    if($res->num_rows > 0){
        $prod = $res->fetch_assoc();
        $image_file = $prod['image'];
        if($conn->query("DELETE FROM products WHERE id=$id")){
            $upload_dir = "uploads/";
            if(!empty($image_file) && file_exists($upload_dir.$image_file)){
                unlink($upload_dir.$image_file);
            }
            $success = "Product deleted successfully!";
            header("Location: admin.php");
            exit;
        } else $error = $conn->error;
    } else $error = "Product not found!";
}

// Fetch all products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel</title>
<style>
body, html{margin:0;padding:0;font-family:Arial;}
.container{display:flex;height:100vh;}
.sidebar{width:220px;background:#007bff;color:white;display:flex;flex-direction:column;padding-top:20px;}
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar button{background:none;border:none;color:white;text-align:left;padding:15px 20px;font-size:16px;cursor:pointer;width:100%;transition:0.3s;}
.sidebar button:hover{background:rgba(255,255,255,0.2);}
.sidebar button.active{background:rgba(255,255,255,0.3);}
.main{flex:1;background:#f4f4f4;padding:20px;overflow-y:auto;}
h1{margin-top:0;}
form{background:white;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);max-width:600px;margin-bottom:20px;}
form label{font-weight:bold;display:block;margin-top:10px;}
form input,form select{width:100%;padding:10px;margin-top:5px;border-radius:4px;border:1px solid #ccc;}
form input[type=submit]{background:#007bff;color:white;border:none;padding:10px 15px;margin-top:15px;cursor:pointer;}
form input[type=submit]:hover{background:#0056b3;}
table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;}
table th,table td{padding:10px;border-bottom:1px solid #ccc;text-align:left;}
table th{background:#007bff;color:white;}
img{width:80px;height:50px;object-fit:cover;border-radius:4px;}
.message{padding:10px;margin-bottom:15px;border-radius:4px;}
.error{background:#f8d7da;color:#721c24;}
.success{background:#d4edda;color:#155724;}
@media(max-width:768px){.container{flex-direction:column;}.sidebar{width:100%;flex-direction:row;overflow-x:auto;}.sidebar button{flex:1;text-align:center;white-space:nowrap;}}
</style>
<script>
function showSection(sectionId){
    const sections=document.querySelectorAll('.section');
    sections.forEach(s=>s.style.display='none');
    const buttons=document.querySelectorAll('.sidebar button');
    buttons.forEach(b=>b.classList.remove('active'));
    document.getElementById(sectionId).style.display='block';
    document.querySelector('button[data-section="'+sectionId+'"]').classList.add('active');
}
window.onload=function(){showSection('dashboard');}
</script>
</head>
<body>
<div class="container">
<div class="sidebar">
<h2>Admin Panel</h2>
<button data-section="add" onclick="showSection('add')">Add Product</button>
<button data-section="update" onclick="showSection('update')">Update Product</button>
<button data-section="delete" onclick="showSection('delete')">Delete Product</button>
<button data-section="dashboard" onclick="showSection('dashboard')">View Dashboard</button>
</div>

<div class="main">
<?php if($error) echo "<div class='message error'>$error</div>"; ?>
<?php if($success) echo "<div class='message success'>$success</div>"; ?>

<!-- Add Product -->
<div id="add" class="section" style="display:none;">
<h1>Add Product</h1>
<form method="post" enctype="multipart/form-data">
<label>Name:</label><input type="text" name="name" required>
<label>Quantity:</label><input type="number" name="quantity" required>
<label>Price:</label><input type="number" name="price" step="0.01" required>
<label>Quality:</label><input type="text" name="quality" required>
<label>Image:</label><input type="file" name="image" accept="image/*" required>
<input type="submit" name="add_product" value="Add Product">
</form>
</div>

<!-- Update Product -->
<div id="update" class="section" style="display:none;">
<h1>Update Product</h1>
<form method="post" enctype="multipart/form-data">
<label>Select Product:</label>
<select name="id" required onchange="this.form.name.value=this.selectedOptions[0].dataset.name;this.form.quantity.value=this.selectedOptions[0].dataset.quantity;this.form.price.value=this.selectedOptions[0].dataset.price;this.form.quality.value=this.selectedOptions[0].dataset.quality;">
<option value="">-- Choose Product --</option>
<?php foreach($products as $row): ?>
<option value="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-quantity="<?php echo $row['quantity']; ?>" data-price="<?php echo $row['price']; ?>" data-quality="<?php echo htmlspecialchars($row['quality']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
<?php endforeach; ?>
</select>
<label>New Name:</label><input type="text" name="name">
<label>New Quantity:</label><input type="number" name="quantity">
<label>New Price:</label><input type="number" name="price" step="0.01">
<label>New Quality:</label><input type="text" name="quality">
<label>Change Image:</label><input type="file" name="image" accept="image/*">
<input type="submit" name="update_product" value="Update Product">
</form>
</div>

<!-- Delete Product -->
<div id="delete" class="section" style="display:none;">
<h1>Delete Product</h1>
<form method="post">
<label>Select Product:</label>
<select name="id" required>
<option value="">-- Choose Product --</option>
<?php
include("connect.php");
$res=$conn->query("SELECT * FROM products");
while($r=$res->fetch_assoc()){
    echo "<option value='".$r['id']."'>".htmlspecialchars($r['name'])."</option>";
}
$conn->close();
?>
</select>
<input type="submit" name="delete_product" value="Delete Product">
</form>
</div>

<!-- Dashboard -->
<div id="dashboard" class="section" style="display:none;">
<h1>Products Dashboard</h1>
<table>
<thead><tr><th>ID</th><th>Name</th><th>Qty</th><th>Price</th><th>Quality</th><th>Image</th></tr></thead>
<tbody>
<?php
include("connect.php");
$all_products=$conn->query("SELECT * FROM products");
while($p=$all_products->fetch_assoc()):
?>
<tr>
<td><?php echo $p['id']; ?></td>
<td><?php echo htmlspecialchars($p['name']); ?></td>
<td><?php echo $p['quantity']; ?></td>
<td>$<?php echo $p['price']; ?></td>
<td><?php echo htmlspecialchars($p['quality']); ?></td>
<td><img src="uploads/<?php echo $p['image']; ?>" alt=""></td>
</tr>
<?php endwhile;$conn->close(); ?>
</tbody>
</table>
</div>

</div>
</div>
</body>
</html>