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
    $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name FROM users WHERE rfid_tag = ? LIMIT 1");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fullName = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);

        echo "OK: Welcome, " . htmlspecialchars($fullName);
    } else {
        savePendingRFID($conn, $uid); 
        echo "REGISTER:" . $uid;
    }

    $stmt->close();
} elseif (isset($_GET['pin'])) {
    $raw = trim($_GET['pin']);

    if (strpos($raw, ":") !== false) {
        list($user_id, $pin_code) = explode(":", $raw);
        $user_id = intval($user_id);

        $stmt = $conn->prepare("SELECT id, pin_code, first_name, middle_name, last_name FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($pin_code, $user['pin_code'])) {
                $fullName = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);

             
                echo "OK: Welcome, " . htmlspecialchars($fullName);
            } else {
                echo "Access Denied";
            }
        } else {
            echo "Access Denied";
        }

        $stmt->close();
    } else {
        echo "Invalid PIN format";
    }
} else {
    echo "Invalid Request";
}

$conn->close();
?>
