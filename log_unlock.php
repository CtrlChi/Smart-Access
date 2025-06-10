<?php
$host = 'localhost';
$db = 'doorlock';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['method'], $_GET['first_name'], $_GET['middle_name'], $_GET['last_name'], $_GET['status'])) {
    $method = strtoupper(trim($conn->real_escape_string($_GET['method'])));
    $first_name = trim($conn->real_escape_string($_GET['first_name']));
    $middle_name = trim($conn->real_escape_string($_GET['middle_name']));
    $last_name = trim($conn->real_escape_string($_GET['last_name']));
    $status = trim($conn->real_escape_string($_GET['status']));

    date_default_timezone_set('Asia/Manila');
    $datetime = date('Y-m-d H:i:s A');

    $user_id = null;

    /* if (strtolower($first_name) !== 'unknown') {
        $sql_user = "SELECT id FROM users WHERE first_name='$first_name' AND middle_name='$middle_name' AND last_name='$last_name' LIMIT 1";
        $result = $conn->query($sql_user);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
        }
    } */
    if (strtolower($first_name) !== 'unknown') {
        $sql_user = "SELECT id FROM users WHERE first_name='$first_name' AND middle_name='$middle_name' AND last_name='$last_name' LIMIT 1";
        $result = $conn->query($sql_user);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
        }
    } else {
        $first_name = $middle_name = $last_name = null;
    }

    $method = strtoupper($method);
    $stmt = $conn->prepare("INSERT INTO logs (user_id, firstname, middlename, lastname, method, status, datetime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $first_name, $middle_name, $last_name, $method, $status, $datetime);

    if ($stmt->execute()) {
        echo "Log entry added successfully";
    } else {
        echo "Error inserting log: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Missing required parameters";
}

$conn->close();
?>



