<?php
require_once 'config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    
    // Validate inputs
    if (empty($name) || empty($price)) {
        $error = 'Name and price are required fields';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Price must be a positive number';
    } else {
        // Handle file upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('product_') . '.' . strtolower($file_ext);
            $target_path = $upload_dir . $file_name;
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_ext), $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image = $file_name;
                } else {
                    $error = 'Failed to upload image';
                }
            } else {
                $error = 'Only JPG, JPEG, PNG, and GIF files are allowed';
            }
        }
        
        if (empty($error)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, stock_quantity) 
                                   VALUES (?, ?, ?, ?, ?)");
            // Corrected bind_param - 5 parameters: name (s), description (s), price (d), image (s), stock_quantity (i)
            $stmt->bind_param("ssdsi", $name, $description, $price, $image, $stock_quantity);
            
            if ($stmt->execute()) {
                $success = 'Product added successfully!';
                // Clear form
                $name = $description = $price = '';
                $stock_quantity = 0;
            } else {
                $error = 'Database error: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!-- The rest of your HTML remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>

    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Add New Product</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price (Rs.) *</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?php echo htmlspecialchars($price ?? ''); ?>" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                        echo htmlspecialchars($description ?? ''); 
                    ?></textarea>
                </div>
                
 
                <div class="row mb-3">
    <div class="col-md-6">
        <label for="stock_quantity" class="form-label">Stock Quantity</label>
        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
               value="<?php echo htmlspecialchars($stock_quantity ?? 0); ?>" min="0">
    </div>
</div>
                
                <div class="mb-4">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <img id="imagePreview" src="#" alt="Preview" class="preview-image img-thumbnail">
                    <small class="text-muted">Max size: 2MB (JPG, PNG, GIF)</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary me-md-2">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                preview.src = '#';
            }
        });
    </script>
        <a href="../Admin/admin_panel.html" class="btn" style="
    position: absolute;
    top: 65px;
    right: 405px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
</body>
</html>
<?php
$conn->close();
?>