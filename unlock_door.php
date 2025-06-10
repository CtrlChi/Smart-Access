<?php
$host = 'localhost';
$db = 'doorlock';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function savePendingRFID($conn, $uid) {
    $stmt = $conn->prepare("SELECT id FROM pending_rfid WHERE uid = ? LIMIT 1");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO pending_rfid (uid) VALUES (?)");
        $insert->bind_param("s", $uid);
        $insert->execute();
        $insert->close();
    } else {
        $stmt->close(); 
    }
}

if (isset($_GET['rfid'])) {
    $uid = strtoupper(trim($conn->real_escape_string($_GET['rfid'])));
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM users WHERE rfid_tag = ? LIMIT 1");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fullName = $user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'];
        echo "OK: Welcome, " . htmlspecialchars($fullName);
    } else {
        savePendingRFID($conn, $uid); 
        echo "REGISTER:" . $uid;
    }

    $stmt->close();
} elseif (isset($_GET['pin'])) {
    $pin = trim($conn->real_escape_string($_GET['pin']));
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM users WHERE pin_code = ? LIMIT 1");
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fullName = $user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'];
        echo "OK: Welcome, " . htmlspecialchars($fullName);
    } else {
        echo "Access Denied";
    }

    $stmt->close();
} else {
    echo "Invalid Request";
}

$conn->close();
?>
