<?php

require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the product ID from POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Check if product exists in database
$stmt = $conn->prepare("SELECT id, stock_quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check stock availability
if (isset($_SESSION['cart'][$product_id])) {
    $requested_quantity = $_SESSION['cart'][$product_id] + 1;
} else {
    $requested_quantity = 1;
}

if ($product['stock_quantity'] < $requested_quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Add product to cart or increment quantity
$_SESSION['cart'][$product_id] = $requested_quantity;

// Calculate total items in cart
$cart_count = array_sum($_SESSION['cart']);

// Return success response
echo json_encode([
    'success' => true,
    'cart_count' => $cart_count,
    'message' => 'Product added to cart successfully'
]);
exit;
?>
