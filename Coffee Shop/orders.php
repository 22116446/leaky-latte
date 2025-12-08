<?php
require 'db.php';

// Get all orders (newest first)
$ordersResult = $conn->query("
    SELECT id, customer_name, customer_email, total_price, created_at
    FROM orders
    ORDER BY created_at DESC
");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Orders - The Leaky Latté</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            text-align: center;
        }
        .order-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fafafa;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .order-meta {
            font-size: 0.9rem;
            color: #555;
        }
        .order-total {
            font-weight: bold;
            font-size: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 0.9rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        .no-orders {
            text-align: center;
            color: #777;
            margin: 40px 0;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <a href="products.html" class="back-link">&larr; Back to shop</a>
    <h1>Orders - The Leaky Latté</h1>

    <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
        <?php
        // Prepare a statement to fetch items for each order
        $itemsStmt = $conn->prepare("
            SELECT product_name, unit_price, quantity
            FROM order_items
            WHERE order_id = ?
        ");
        ?>

        <?php while ($order = $ordersResult->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-meta">
                            <strong>Order #<?php echo htmlspecialchars($order['id']); ?></strong><br>
                            Placed: <?php echo htmlspecialchars($order['created_at']); ?>
                        </div>
                        <div class="order-meta">
                            Customer: <?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?><br>
                            Email: <?php echo htmlspecialchars($order['customer_email'] ?: 'N/A'); ?>
                        </div>
                    </div>
                    <div class="order-total">
                        Total: ₺<?php echo htmlspecialchars(number_format($order['total_price'], 2)); ?>
                    </div>
                </div>

                <?php
                // Fetch items for this order
                $itemsStmt->bind_param("i", $order['id']);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                ?>

                <?php if ($itemsResult && $itemsResult->num_rows > 0): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Unit price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($item = $itemsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>₺<?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>
                                    ₺<?php
                                    $subtotal = $item['unit_price'] * $item['quantity'];
                                    echo htmlspecialchars(number_format($subtotal, 2));
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="order-meta">No items for this order.</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php $itemsStmt->close(); ?>

    <?php else: ?>
        <p class="no-orders">No orders have been placed yet.</p>
    <?php endif; ?>

</div>
</body>
</html>

<?php
$conn->close();
?>
