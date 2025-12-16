<?php
require __DIR__ . '/db.php';
require 'db.php';
require 'auth.php';

require_login();             // only logged-in users can see this page
$user = current_user();

// ---------- Handle status update (admin + staff) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (in_array($user['role'], ['admin', 'staff'], true)) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status  = $_POST['status'] ?? 'Pending';

        if ($orderId > 0 && in_array($status, ['Pending', 'Completed'], true)) {
            $stmt = $conn->prepare("UPDATE public.orders SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $orderId]);
        }
    }
    // Redirect to avoid resubmission on refresh
    header('Location: orders.php?' . http_build_query($_GET));
    exit;
}

// ---------- Build query with filters / visibility ----------

$where  = [];
$params = [];

// ADMIN: optional filters on date + status
if ($user['role'] === 'admin') {
    if (!empty($_GET['date'])) {
        $where[]  = "created_at::date = :date";
        $params[':date'] = $_GET['date'];
    }

    if (!empty($_GET['status']) && in_array($_GET['status'], ['Pending','Completed'], true)) {
        $where[] = "status = :status";
        $params[':status'] = $_GET['status'];
    }
}

// CLIENT: see only their own orders (by email)
if ($user['role'] === 'client') {
    $where[] = "customer_email = :email";
    $params[':email'] = $user['email'];
}

// STAFF: see all orders, no extra filters (for now)

$sql = "
    SELECT id, customer_name, customer_email, total_price, created_at, status
    FROM public.orders
";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY created_at DESC";

$stmtOrders = $conn->prepare($sql);
$stmtOrders->execute($params);
$ordersResult = $stmtOrders->fetchAll();

// Prepare statement for items
$itemsStmt = $conn->prepare(
    "SELECT product_name, unit_price, quantity
     FROM public.order_items
     WHERE order_id = :order_id"
);
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
        .navbar {
            background: #3c2a21;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar .logo {
            font-weight: bold;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            margin-right: 15px;
            font-size: 0.95rem;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .navbar .right {
            font-size: 0.85rem;
        }
        .wrapper {
            max-width: 1000px;
            margin: 30px auto;
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
            text-align: right;
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
        .filters-bar {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filters-bar form label {
            font-size: 0.85rem;
            display: block;
        }
        .filters-bar input, .filters-bar select {
            font-size: 0.85rem;
            padding: 2px 4px;
        }
        .status-form {
            margin-top: 8px;
            font-size: 0.85rem;
        }
        .status-form select {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<!-- Role-based navbar -->
<div class="navbar">
    <div class="logo">The Leaky Latté</div>
    <div>
        <!-- Visible to everyone (logged in or not normally, but here we are logged in already) -->
        <a href="index.html">Home</a>
        <a href="products.html">Products</a>
        <a href="about us.html">About Us</a>
        <a href="contact us.html">Contact Us</a>

        <?php if ($user['role'] === 'admin'): ?>
            <a href="orders.php">All Orders</a>
            <a href="admin_dashboard.php">Admin Dashboard</a>
        <?php elseif ($user['role'] === 'staff'): ?>
            <a href="orders.php">All Orders</a>
        <?php elseif ($user['role'] === 'client'): ?>
            <a href="orders.php">My Orders</a>
        <?php endif; ?>
    </div>
    <div class="right">
        Logged in as
        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        (<?php echo htmlspecialchars($user['role']); ?>)
        | <a href="logout.php">Logout</a>
    </div>
</div>

<div class="wrapper">
    <h1>Orders - The Leaky Latté</h1>

    <!-- Admin-only filters -->
    <div class="filters-bar">
        <?php if ($user['role'] === 'admin'): ?>
            <form method="get">
                <label>
                    Date:
                    <input type="date" name="date"
                           value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                </label>

                <label>
                    Status:
                    <select name="status">
                        <option value="">All</option>
                        <option value="Pending"   <?php if(($_GET['status'] ?? '') === 'Pending')   echo 'selected'; ?>>Pending</option>
                        <option value="Completed" <?php if(($_GET['status'] ?? '') === 'Completed') echo 'selected'; ?>>Completed</option>
                    </select>
                </label>

                <button type="submit">Filter</button>
                <a href="orders.php" style="margin-left:5px;">Clear</a>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($ordersResult)): ?>

        <?php foreach ($ordersResult as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-meta">
                            <strong>Order #<?php echo (int)$order['id']; ?></strong><br>
                            Placed: <?php echo htmlspecialchars($order['created_at']); ?>
                        </div>
                        <div class="order-meta">
                            Customer: <?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?><br>
                            Email: <?php echo htmlspecialchars($order['customer_email'] ?: 'N/A'); ?>
                        </div>
                    </div>
                    <div class="order-total">
                        Total: ₺<?php echo htmlspecialchars(number_format($order['total_price'], 2)); ?><br>
                        Status: <strong><?php echo htmlspecialchars($order['status']); ?></strong>
                    </div>
                </div>

                <?php
                // Fetch items for this order
                $itemsStmt->execute([':order_id' => $order['id']]);
                $itemsResult = $itemsStmt->fetchAll();
                ?>

                <?php if (!empty($itemsResult)): ?>
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
                        <?php foreach ($itemsResult as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>₺<?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                                <td><?php echo (int)$item['quantity']; ?></td>
                                <td>
                                    ₺<?php
                                    $subtotal = $item['unit_price'] * $item['quantity'];
                                    echo htmlspecialchars(number_format($subtotal, 2));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="order-meta">No items for this order.</p>
                <?php endif; ?>

                <?php if (in_array($user['role'], ['admin','staff'], true)): ?>
                    <form method="post" class="status-form">
                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                        <label>
                            Update status:
                            <select name="status">
                                <option value="Pending"   <?php if($order['status']==='Pending')   echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if($order['status']==='Completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </label>
                        <button type="submit" name="update_status">Save</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p class="no-orders">No orders found.</p>
    <?php endif; ?>

</div>
</body>
</html>
<?php
// PDO auto-closes at end of request
?>
