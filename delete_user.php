<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
$deleted = false;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $deleted = $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .message-box h2 {
            color: #4eb6a5;
            margin-bottom: 15px;
        }
        .message-box a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background: #4eb6a5;
            padding: 10px 20px;
            border-radius: 6px;
        }
        .message-box a:hover {
            background: #3ca590;
        }
    </style>
</head>
<body>
<div class="message-box">
    <?php if ($deleted): ?>
        <h2>User deleted successfully.</h2>
    <?php else: ?>
        <h2>Failed to delete user.</h2>
    <?php endif; ?>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
