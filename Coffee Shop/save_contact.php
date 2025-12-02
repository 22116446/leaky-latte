<?php
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

$stmt = $conn->prepare("INSERT INTO contacts (full_name, email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $fullName, $email, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error']);
}

$stmt->close();
$conn->close();
?>
