<?php
include 'config.php'; // DB connection

$query = "SELECT 
            o.id AS order_id,
            o.first_name,
            o.last_name,
            o.email,
            o.phone,
            o.total AS order_total,
            o.payment_method,
            o.created_at,
            d.product_id,
            d.quantity,
            d.price,
            d.total_price
          FROM orders o
          LEFT JOIN order_details d ON o.id = d.order_id
          ORDER BY o.id DESC, d.id ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders with Details</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        tr.order-row { background-color: #e9f7ef; font-weight: bold; }
    </style>
</head>
<body>
    <h2>All Orders with Details</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email / Phone</th>
                <th>Product ID</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Item Total</th>
                <th>Order Total</th>
                <th>Payment</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $prevOrderId = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $isNewOrder = $prevOrderId != $row['order_id'];
                $prevOrderId = $row['order_id'];
            ?>
                <tr class="<?= $isNewOrder ? 'order-row' : '' ?>">
                    <td>#<?= $row['order_id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email'] . ' / ' . $row['phone']) ?></td>
                    <td><?= $row['product_id'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>Rs.<?= number_format($row['price'], 2) ?></td>
                    <td>Rs.<?= number_format($row['total_price'], 2) ?></td>
                    <td><?= $isNewOrder ? 'Rs.' . number_format($row['order_total'], 2) : '' ?></td>
                    <td><?= $isNewOrder ? strtoupper($row['payment_method']) : '' ?></td>
                    <td><?= $isNewOrder ? $row['created_at'] : '' ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
        <a href="../Admin/admin_panel.html" class="btn" style="
    position: absolute;
    top: 45px;
    right: 35px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
</body>
</html>
