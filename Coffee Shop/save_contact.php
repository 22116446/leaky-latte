<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');
require 'db.php';

$fullName = $_POST['fullName'] ?? '';
$email    = $_POST['email'] ?? '';
$message  = $_POST['message'] ?? '';

if ($fullName === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO public.contacts (full_name, email, message) VALUES (:n, :e, :m)");
    $stmt->execute([':n' => $fullName, ':e' => $email, ':m' => $message]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error']);
}
?>
