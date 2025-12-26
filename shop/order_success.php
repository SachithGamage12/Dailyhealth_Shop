<?php
require_once 'config.php';


// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify checkout was completed
if (empty($_SESSION['valid_checkout'])) {
    header('Location: index.php');
    exit;
}

// Get order details from session
$order_id = $_SESSION['order_id'] ?? null;
$order_details = $_SESSION['order_details'] ?? [];

// Clear session data immediately
unset($_SESSION['valid_checkout']);
unset($_SESSION['order_id']);
unset($_SESSION['order_details']);

// Fetch order items from database
$order_items = [];
if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $order_items[] = $item;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - DailyHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .thank-you-message {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Thank You Message -->
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="mb-3 thank-you-message">Thank You for Your Order!</h1>
                <p class="lead mb-4">Your order #<?= htmlspecialchars($order_details['order_number'] ?? $order_id) ?> has been placed successfully.</p>
                
                <!-- Order Summary -->
                <div class="order-details text-start">
                    <h4 class="mb-4">Order Summary</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Shipping Information</h5>
                            <p>
                                <strong><?= htmlspecialchars($order_details['first_name'] ?? '') ?> <?= htmlspecialchars($order_details['last_name'] ?? '') ?></strong><br>
                                <?= htmlspecialchars($order_details['address'] ?? '') ?><br>
                                <?= htmlspecialchars($order_details['city'] ?? '') ?>, 
                                <?= htmlspecialchars($order_details['state'] ?? '') ?> 
                                <?= htmlspecialchars($order_details['zip'] ?? '') ?><br>
                                Phone: <?= htmlspecialchars($order_details['phone'] ?? '') ?><br>
                                Email: <?= htmlspecialchars($order_details['email'] ?? '') ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Method</h5>
                            <p>
                                <?php 
                                $payment_methods = [
                                    'credit_card' => 'Credit Card',
                                    'paypal' => 'PayPal',
                                    'cod' => 'Cash on Delivery'
                                ];
                                $method = $order_details['payment_method'] ?? 'N/A';
                                echo htmlspecialchars($payment_methods[$method] ?? $method); 
                                ?>
                            </p>
                            <h5>Order Total</h5>
                            <p>Rs. <?= number_format($order_details['total'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <h5 class="mt-4">Order Items</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></td>
                                    <td class="text-end">Rs. <?= number_format($item['price'] ?? 0, 2) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?? 0 ?></td>
                                    <td class="text-end">Rs. <?= number_format($item['total_price'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <td class="text-end">Rs. <?= number_format($order_details['subtotal'] ?? $order_details['total'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <td class="text-end">Rs. <?= number_format($order_details['total'] ?? 0, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i> Continue Shopping
                    </a>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="fas fa-clipboard-list me-2"></i> View Order History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>