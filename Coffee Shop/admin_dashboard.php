<?php
require 'db.php';
require 'auth.php';

require_role(['admin']);
$user = current_user();

// Basic stats
// Total orders
$resTotal = $conn->query("SELECT COUNT(*) AS c FROM public.orders");
$totalOrders = ($resTotal->fetch()['c'] ?? 0);

// Pending
$resPending = $conn->query("SELECT COUNT(*) AS c FROM public.orders WHERE status = 'Pending'");
$pendingOrders = ($resPending->fetch()['c'] ?? 0);

// Completed
$resCompleted = $conn->query("SELECT COUNT(*) AS c FROM public.orders WHERE status = 'Completed'");
$completedOrders = ($resCompleted->fetch()['c'] ?? 0);

// Total revenue
$resRevenue = $conn->query("SELECT COALESCE(SUM(total_price),0) AS s FROM public.orders");
$totalRevenue = ($resRevenue->fetch()['s'] ?? 0);

// Today's orders
$resToday = $conn->query("
    SELECT COUNT(*) AS c, COALESCE(SUM(total_price),0) AS s
    FROM public.orders
    WHERE created_at::date = CURRENT_DATE
");
$todayRow      = $resToday->fetch();
$todayOrders   = $todayRow['c'] ?? 0;
$todayRevenue  = $todayRow['s'] ?? 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - The Leaky Latté</title>
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
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .card {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
        }
        .card h2 {
            margin: 0 0 10px 0;
            font-size: 1rem;
        }
        .card .value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .links {
            margin-top: 20px;
            text-align: center;
        }
        .links a {
            margin: 0 10px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">The Leaky Latté</div>
    <div>
        <a href="index.html">Home</a>
        <a href="products.html">Products</a>
        <a href="about us.html">About Us</a>
        <a href="contact us.html">Contact Us</a>
        <a href="orders.php">All Orders</a>
        <a href="admin_dashboard.php">Admin Dashboard</a>
    </div>
    <div class="right">
        Logged in as
        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        (<?php echo htmlspecialchars($user['role']); ?>)
        | <a href="logout.php">Logout</a>
    </div>
</div>

<div class="wrapper">
    <h1>Admin Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h2>Total Orders</h2>
            <div class="value"><?php echo (int)$totalOrders; ?></div>
        </div>
        <div class="card">
            <h2>Pending Orders</h2>
            <div class="value"><?php echo (int)$pendingOrders; ?></div>
        </div>
        <div class="card">
            <h2>Completed Orders</h2>
            <div class="value"><?php echo (int)$completedOrders; ?></div>
        </div>
        <div class="card">
            <h2>Total Revenue</h2>
            <div class="value">₺<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="card">
            <h2>Today's Orders</h2>
            <div class="value"><?php echo (int)$todayOrders; ?></div>
        </div>
        <div class="card">
            <h2>Today's Revenue</h2>
            <div class="value">₺<?php echo number_format($todayRevenue, 2); ?></div>
        </div>
    </div>

    <div class="links">
        <a href="orders.php">View all orders</a>
    </div>
</div>

</body>
</html>
<?php
// PDO auto-closes at end of request
?>
