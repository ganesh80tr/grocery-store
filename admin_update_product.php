<?php

// Include configuration file
@include 'config.php';

// Start session
session_start();

// Check if admin is logged in
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   // Redirect to login page if admin is not logged in
   header('location:login.php');
   exit(); // Always exit after a redirect
}

// Check if the form for updating product is submitted
if (isset($_POST['update_product'])) {

   // Retrieve product ID from the form
   $pid = $_POST['pid'];

   // Retrieve and sanitize product name
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   // Retrieve and sanitize product price
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);

   // Retrieve and sanitize product category
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);

   // Retrieve and sanitize product details
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   // Retrieve image data
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;
   $old_image = $_POST['old_image'];

   // Prepare and execute product update query
   $update_product = $conn->prepare("UPDATE `products` SET name =?, category =?, details =?, price =? WHERE id =?");
   $update_product->execute([$name, $category, $details, $price, $pid]);

   // Add success message
   $message[] = 'Product updated successfully!';

   // Check if a new image is uploaded
   if (!empty($image)) {
      if ($image_size > 2000000) {
         $message[] = 'Image size is too large!';
      } else {

         // Prepare and execute image update query
         $update_image = $conn->prepare("UPDATE `products` SET image =? WHERE id =?");
         $update_image->execute([$image, $pid]);

         // If image update query is successful, move the uploaded image to the folder
         if ($update_image) {
            move_uploaded_file($image_tmp_name, $image_folder);
            // Check if the old image file exists before unlinking it
            if (file_exists('uploaded_img/' . $old_image)) {
               unlink('uploaded_img/' . $old_image);
            }
            $message[] = 'Image updated successfully!';
         }
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Products</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php';?>

   <section class="update-product">

      <h1 class="title">Update Product</h1>

      <?php
      // Check if product ID is provided in the URL
      if (isset($_GET['update'])) {
         // Retrieve product ID from URL
         $update_id = $_GET['update'];

         // Prepare and execute query to select product by ID
         $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
         $select_products->execute([$update_id]);

         // Check if product exists
         if ($select_products->rowCount() > 0) {
            // Fetch product details
            $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
          ?>
            <form action="" method="post" enctype="multipart/form-data">
               <input type="hidden" name="old_image" value="<?= $fetch_product['image'];?>">
               <input type="hidden" name="pid" value="<?= $fetch_product['id'];?>">
               <img src="uploaded_img/<?= $fetch_product['image'];?>" alt="">
               <input type="text" name="name" placeholder="Enter product name" required class="box"
                  value="<?= $fetch_product['name'];?>">
               <input type="number" name="price" min="0" placeholder="Enter product price" required class="box"
                  value="<?= $fetch_product['price'];?>">
               <select name="category" class="box" required>
                  <option value="vegetables" <?= ($fetch_product['category'] == 'vegetables') ? 'selected' : '';?>>Vegetables</option>
                  <option value="fruits" <?= ($fetch_product['category'] == 'fruits') ? 'selected' : '';?>>Fruits</option>
               </select>
               <textarea name="details" required placeholder="Enter product details" class="box" cols="30"
                  rows="10"><?= $fetch_product['details'];?></textarea>
               <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
               <div class="flex-btn">
                  <input type="submit" class="btn" value="Update Product" name="update_product">
                  <a href="admin_products.php" class="option-btn">Go Back</a>
               </div>
            </form>
            <?php
         } else {
            echo '<p class="empty">No products found!</p>';
         }
      } else {
         echo '<p class="empty">Product ID not provided!</p>';
      }
    ?>

   </section>

   <script src="js/script.js"></script>

</body>

</html>
