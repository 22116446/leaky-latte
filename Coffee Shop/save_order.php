<?php
// (Optional during development; set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require 'db.php';

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid JSON',
    ]);
    exit;
}

$customerName  = $data['customerName']  ?? '';
$customerEmail = $data['customerEmail'] ?? '';
$items         = $data['items']         ?? [];
$total         = $data['total']         ?? 0;

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty cart']);
    exit;
}

try {
    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Could not start transaction: ' . $conn->error);
    }

    // Insert into orders
    $stmtOrder = $conn->prepare(
        "INSERT INTO orders (customer_name, customer_email, total_price) VALUES (?, ?, ?)"
    );
    if (!$stmtOrder) {
        throw new Exception('Prepare failed for orders: ' . $conn->error);
    }

    $stmtOrder->bind_param("ssd", $customerName, $customerEmail, $total);

    if (!$stmtOrder->execute()) {
        throw new Exception('Execute failed for orders: ' . $stmtOrder->error);
    }

    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    // Insert order items
    $stmtItem = $conn->prepare(
        "INSERT INTO order_items (order_id, product_name, unit_price, quantity)
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmtItem) {
        throw new Exception('Prepare failed for order_items: ' . $conn->error);
    }

    foreach ($items as $item) {
        $name     = $item['name'];
        $price    = $item['price'];
        $quantity = $item['quantity'];

        $stmtItem->bind_param("isdi", $orderId, $name, $price, $quantity);
        if (!$stmtItem->execute()) {
            throw new Exception('Execute failed for order_items: ' . $stmtItem->error);
        }
    }

    $stmtItem->close();
    $conn->commit();

    echo json_encode(['success' => true, 'orderId' => $orderId]);

} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'DB error: ' . $e->getMessage()
    ]);
}

$conn->close();
