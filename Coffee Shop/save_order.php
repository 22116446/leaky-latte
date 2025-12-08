<?php
header('Content-Type: application/json');
require 'db.php';

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
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

$conn->begin_transaction();

try {
    // Insert into orders
    $stmtOrder = $conn->prepare(
        "INSERT INTO orders (customer_name, customer_email, total_price) VALUES (?, ?, ?)"
    );
    $stmtOrder->bind_param("ssd", $customerName, $customerEmail, $total);
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    // Insert order items
    $stmtItem = $conn->prepare(
        "INSERT INTO order_items (order_id, product_name, unit_price, quantity)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {
        $name     = $item['name'];
        $price    = $item['price'];
        $quantity = $item['quantity'];

        $stmtItem->bind_param("isdi", $orderId, $name, $price, $quantity);
        $stmtItem->execute();
    }

    $stmtItem->close();
    $conn->commit();

    echo json_encode(['success' => true, 'orderId' => $orderId]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error']);
}

$conn->close();
?>
