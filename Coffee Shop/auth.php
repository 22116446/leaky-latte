<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_role(array $roles) {
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        // Forbidden
        http_response_code(403);
        echo "Access denied.";
        exit;
    }
}
