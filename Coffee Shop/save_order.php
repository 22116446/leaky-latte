<?php
require __DIR__ . '/db.php';
// (Optional during development; set display_errors to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require 'db.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$customerName  = trim($data['customerName'] ?? '');
$customerEmail = trim($data['customerEmail'] ?? '');
$items         = $data['items'] ?? [];

if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty cart']);
    exit;
}

// Basic input sanitation
foreach ($items as $it) {
    if (!isset($it['name'], $it['price'], $it['quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid item']);
        exit;
    }
}

try {
    $conn->beginTransaction();

    // Create an order row (total_price will be recomputed by trigger after items insert)
    $stmtOrder = $conn->prepare(
        "INSERT INTO public.orders (customer_name, customer_email, total_price)
         VALUES (:name, :email, 0)
         RETURNING id"
    );
    $stmtOrder->execute([
        ':name'  => $customerName,
        ':email' => $customerEmail,
    ]);
    $orderId = (int)$stmtOrder->fetchColumn();

    $stmtItem = $conn->prepare(
        "INSERT INTO public.order_items (order_id, product_name, unit_price, quantity)
         VALUES (:order_id, :product_name, :unit_price, :quantity)"
    );

    foreach ($items as $it) {
        $stmtItem->execute([
            ':order_id'     => $orderId,
            ':product_name' => (string)$it['name'],
            ':unit_price'   => (float)$it['price'],
            ':quantity'     => (int)$it['quantity'],
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'orderId' => $orderId]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error']);
}
