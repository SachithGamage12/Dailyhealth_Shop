<?php
require_once 'config.php';


// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $address    = $_POST['address'];
    $city       = $_POST['city'];
    $state      = $_POST['state'];
    $zip        = $_POST['zip'];
    $payment_method = $_POST['payment_method'];

    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $result = $conn->query("SELECT id, price FROM products WHERE id IN ($product_ids)");

    if (!$result) die("Error fetching products: " . $conn->error);

    $subtotal = 0;
    $product_data = [];
    while ($row = $result->fetch_assoc()) {
        $qty = $_SESSION['cart'][$row['id']];
        $total_price = $row['price'] * $qty;
        $subtotal += $total_price;

        $product_data[] = [
            'id' => $row['id'],
            'price' => $row['price'],
            'quantity' => $qty,
            'total_price' => $total_price
        ];
    }

    $total = $subtotal;

    $stmt = $conn->prepare("INSERT INTO orders 
        (first_name, last_name, email, phone, address, city, state, zip, payment_method, total) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssd", $first_name, $last_name, $email, $phone, $address, $city, $state, $zip, $payment_method, $total);
    $stmt->execute();

    if ($stmt->affected_rows === 0) die("Error executing order insert: " . $stmt->error);

    $order_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($product_data as $item) {
        $stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $item['total_price']);
        $stmt->execute();
    }
    $stmt->close();

    $_SESSION['valid_checkout'] = true;
    $_SESSION['order_id'] = $order_id;
    $_SESSION['order_details'] = [
        'order_number' => 'ORD-' . str_pad($order_id, 8, "0", STR_PAD_LEFT),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'payment_method' => $payment_method,
        'subtotal' => $subtotal,
        'total' => $total
    ];

    unset($_SESSION['cart']);
    header('Location: order_success.php');
    exit;
}

// Load cart products for display
$cart_items = [];
$subtotal = 0;

$product_ids = implode(',', array_keys($_SESSION['cart']));
$result = $conn->query("SELECT id, name, price FROM products WHERE id IN ($product_ids)");

while ($product = $result->fetch_assoc()) {
    $qty = $_SESSION['cart'][$product['id']];
    $total_price = $product['price'] * $qty;
    $subtotal += $total_price;

    $cart_items[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $qty,
        'total_price' => $total_price
    ];
}

$total = $subtotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - DailyHealth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <form method="post" class="card shadow-sm p-4">
                <h4 class="mb-4">Shipping Information</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address *</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State *</label>
                        <input type="text" name="state" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ZIP *</label>
                        <input type="text" name="zip" class="form-control" required>
                    </div>
                </div>

                <h4 class="mt-4">Payment Method</h4>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="payment_method" value="cod" required>
                    <label class="form-check-label">Cash on Delivery</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" required>
                    <label class="form-check-label">I agree to the <a href="#">terms & conditions</a></label>
                </div>

                <button type="submit" class="btn btn-success w-100">Place Order</button>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm p-3">
                <h4 class="mb-3">Order Summary</h4>
                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong><?= htmlspecialchars($item['name']); ?></strong><br>
                            <small>Qty: <?= $item['quantity']; ?></small>
                        </div>
                        <div>Rs.<?= number_format($item['total_price'], 2); ?></div>
                    </div>
                    <hr>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total:</span>
                    <span>Rs.<?= number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
