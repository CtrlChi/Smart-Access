<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost:3306", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin_dashboard.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $rfid_tag = trim($_POST['rfid_tag']);
    $pin_code = $_POST['pin_code'] ?? '';
    $role = $_POST['role'];

    if (!empty($pin_code)) {
        
        $hashed_pin = password_hash($pin_code, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, rfid_tag=?, pin_code=?, role=? WHERE id=?");
        $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $rfid_tag, $hashed_pin, $role, $id);
    } else {
        
        $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, rfid_tag=?, role=? WHERE id=?");
        $stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $rfid_tag, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?message=User+updated+successfully&type=success");
        exit;
    } else {
        header("Location: admin_dashboard.php?message=Error+updating+user&type=error");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap');

    * {
        box-sizing: border-box;
        font-family: 'Orbitron', sans-serif;
    }

    body {
        background: linear-gradient(to right, rgb(15, 17, 18), #203a43, #2c5364);
        color: #e0e0e0;
        margin: 0;
        padding: 60px 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        flex-direction: column;
    }

    body::before {
        content: "";
        background: url('2.png') no-repeat center center fixed;
        background-size: 130% 130%;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        opacity: 0.2;
    }

    .form-box {
        background: #1b1f22;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 400px;
    }

    .form-box h2 {
        color: #00bfff;
        margin-bottom: 25px;
        text-align: center;
    }

    label {
        display: block;
        margin-bottom: 10px;
        color: #e0e0e0;
        font-weight: bold;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #333;
        border-radius: 6px;
        margin-bottom: 20px;
        background: #101820;
        color: #e0e0e0;
    }

    input:focus, select:focus {
        outline: none;
        border-color: #00bfff;
        box-shadow: 0 0 6px #00bfff;
    }

    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        padding-right: 40px;
    }

    .toggle-eye {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
        font-size: 18px;
        color: #999;
    }

    .toggle-eye:hover {
        color: #00bfff;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #00bfff;
        color: #101820;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    button:hover {
        background: #0094cc;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        background: #00bfff;
        color: #101820;
        padding: 10px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
    }

    .back-link:hover {
        background: #0094cc;
    }

    input[readonly] {
        background-color: #333;
        color: #888;
        cursor: not-allowed;
    }

</style>
</head>
<body>
<div class="form-box">
    <h2>Edit User</h2>
    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required />

        <label>Middle Name:</label>
        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>" />

        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required />

        <label>RFID Tag:</label>
        <input type="text" name="rfid_tag" value="<?php echo htmlspecialchars($user['rfid_tag']); ?>" readonly />

        <label>New PIN (leave blank to keep current):</label>
        <div class="password-wrapper">
            <input
                type="password"
                name="pin_code"
                id="pin_code"
                maxlength="4"
                inputmode="numeric"
                pattern="\d{4}"
                onkeypress="return onlyNumberKey(event)"
                placeholder="Enter new 4-digit PIN"
            />
            <span class="toggle-eye" onclick="togglePIN()">👁️</span>
        </div>

        <label>Role:</label>
        <select name="role" required>
            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>

    <a href="admin_dashboard.php" class="back-link">⬅️ Back to Dashboard</a>
</div>

<script>
function togglePIN() {
    const input = document.getElementById("pin_code");
    input.type = input.type === "password" ? "text" : "password";
}

function onlyNumberKey(evt) {
    var ASCIICode = evt.which ? evt.which : evt.keyCode;
    if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) {
        evt.preventDefault();
        return false;
    }
    return true;
}
</script>
</body>
</html>
