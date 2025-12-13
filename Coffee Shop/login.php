<?php
require 'db.php';
require 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $error = 'Please enter username/email and password.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, username, email, password_hash, role
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ];
            header('Location: products.html'); // or orders.php for admin/staff
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - The Leaky Latté</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background:#f6f6f6; }
        .box { max-width: 400px; margin:80px auto; background:#fff; padding:20px 25px;
               border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        h1 { margin-top:0; text-align:center; }
        label { display:block; margin-top:10px; }
        input[type=text], input[type=password] {
            width:100%; padding:8px; margin-top:4px; box-sizing:border-box;
        }
        button { margin-top:15px; width:100%; padding:10px; }
        .error { color:#b00; margin-top:10px; text-align:center; }
        .link { margin-top:10px; text-align:center; font-size:0.9rem; }
    </style>
</head>
<body>
<div class="box">
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Username or email
            <input type="text" name="username" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Login</button>
    </form>
    <div class="link">
        <a href="index.html">Back to home</a>
    </div>
</div>
</body>
</html>
