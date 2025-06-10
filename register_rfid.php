<?php
$rfid = $_GET['rfid'] ?? '';

if ($rfid) {
    $conn = new mysqli("localhost", "root", "", "doorlock");
    if ($conn->connect_error) {
        die("Connection failed");
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO pending_rfid (uid) VALUES (?)");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: register_user.php?rfid=" . urlencode($rfid));
    exit();
} else {
    echo "Missing RFID.";
}
?>
