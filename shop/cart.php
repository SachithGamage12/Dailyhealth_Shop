<?php
require_once 'config.php';

$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image, p.stock_quantity, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);


// Initialize variables
$cart_items = [];
$subtotal = 0;
$total = 0;
$error = null;

// Remove item from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['message'] = "Item removed from cart";
    header('Location: cart.php');
    exit;
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            continue;
        }

        $stmt = $conn->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            if ($quantity > $product['stock_quantity']) {
                $error = "Only {$product['stock_quantity']} items available for {$product['name']}";
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        } else {
            unset($_SESSION['cart'][$product_id]);
            $error = "Some products were removed as they're no longer available";
        }
        $stmt->close();
    }

    if (!isset($error)) {
        $_SESSION['message'] = "Cart updated successfully";
    }
}

// Fetch cart items
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $product_ids = array_keys($_SESSION['cart']);

    $sql = "SELECT id, name, price, image, stock_quantity FROM products WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    $types = str_repeat('i', count($product_ids));
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['id']];
        $max_quantity = min($quantity, $product['stock_quantity']);
        $total_price = $product['price'] * $max_quantity;

        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'] ?? 'default.jpg',
            'quantity' => $max_quantity,
            'stock_quantity' => $product['stock_quantity'],
            'total_price' => $total_price
        ];

        $subtotal += $total_price;
    }

    foreach ($cart_items as $item) {
        $_SESSION['cart'][$item['id']] = $item['quantity'];
    }

    $total = $subtotal;
    $stmt->close();
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - DailyHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .empty-cart-icon {
            font-size: 4rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Your Shopping Cart</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart empty-cart-icon mb-4"></i>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added anything yet.</p>
            <a href="products.php" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-left me-2"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <form method="post">
            <div class="row gy-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="uploads/products/<?php echo htmlspecialchars($item['image']); ?>" class="product-img me-3 rounded" alt="">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                                            <small class="text-muted">Stock: <?php echo $item['stock_quantity']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>Rs.<?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" class="form-control quantity-input">
                                                </td>
                                                <td>Rs.<?php echo number_format($item['total_price'], 2); ?></td>
                                                <td>
                                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                                </a>
                                <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-2"></i> Update Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Order Summary</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>Rs.<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>Free</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>Rs.0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total:</span>
                                <span>Rs.<?php echo number_format($total, 2); ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100 mt-3">
                                Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                            <div class="mt-4 text-center">
                                <p class="text-muted small">We accept:</p>
                                <img src="assets/images/payment-methods.png" alt="Payments" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>  
