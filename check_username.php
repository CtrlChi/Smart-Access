<?php
$conn = new mysqli("localhost", "root", "", "doorlock");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$username = $_GET['username'] ?? '';
$username = trim($username);

if (strlen($username) < 3) {
    echo json_encode(['available' => false, 'suggestion' => '']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['available' => true]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

function generateSuggestion($base, $conn) {
    for ($i = 1; $i < 1000; $i++) {
        $suggestion = $base . $i;
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $suggestion);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            return $suggestion;
        }
        $stmt->close();
    }
    return $base . rand(1000,9999);
}

$suggestion = generateSuggestion($username, $conn);
$conn->close();

echo json_encode(['available' => false, 'suggestion' => $suggestion]);
?>
