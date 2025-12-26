<?php
// Database connection details
$servername = "localhost";
$username = "u627928174_root";
$password = "Daily@365";
$dbname = "u627928174_daily_routine";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $target = "images/" . basename($image);

    // Upload image
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Insert product into database using prepared statements
        $sql = "INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssds", $name, $description, $price, $image);
            if ($stmt->execute()) {
                echo "Product added successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}

// Check if ID is provided
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Product Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .back-button {
            background: none;
            border: none;
            color: #6c5ce7;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: rgba(108, 92, 231, 0.1);
        }
        
        .product-details {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 768px) {
            .product-details {
                flex-direction: row;
            }
        }
        
        .product-image {
            flex: 1;
            max-width: 100%;
        }
        
        @media (min-width: 768px) {
            .product-image {
                max-width: 50%;
            }
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #2d3436;
        }
        
        .product-description {
            color: #636e72;
            margin-bottom: 2rem;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #6c5ce7;
            margin-bottom: 1.5rem;
        }
        
        .add-to-cart {
            background: linear-gradient(to right, rgb(76, 30, 228), #a29bfe);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1.1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            align-self: flex-start;
        }
        
        .add-to-cart:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 19, 19, 0.5);
        }
    </style>
</head>
<body>
    <header>
        <button class="back-button" onclick="window.location.href='shop_index.php'">
            ← Back to Products
        </button>
    </header>
    
    <div class="product-details">
        <div class="product-image">
            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="product-info">
            <h1 class="product-title"><?php echo $product['name']; ?></h1>
            <p class="product-description"><?php echo $product['description']; ?></p>
            <p class="product-price">Rs.<?php echo $product['price']; ?></p>
            <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Buy Now</button>
        </div>
    </div>
</body>
</html>