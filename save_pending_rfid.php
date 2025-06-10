<?php
$conn = new mysqli("localhost", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = $_GET['uid'] ?? '';

if (!empty($uid)) {
    $stmt = $conn->prepare("INSERT INTO pending_rfid (uid) VALUES (?)");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    echo "SAVED";
    $stmt->close();
} else {
    echo "NO UID";
}

$conn->close();
?>
