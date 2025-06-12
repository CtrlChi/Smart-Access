<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost:3307", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$user_id = $_SESSION['user_id'];

$method_filter = $_GET['method_filter'] ?? '';


$query = "SELECT method, status, datetime FROM logs WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($method_filter && in_array($method_filter, ['RFID', 'PIN'])) {
    $query .= " AND method = ?";
    $params[] = $method_filter;
    $types .= "s";
}


$query .= " ORDER BY datetime DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$logs_result = $stmt->get_result();
$stmt->close();


$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, rfid_tag, pin_code, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$username = $user['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_first_name = trim($_POST['first_name'] ?? '');
    $new_middle_name = trim($_POST['middle_name'] ?? '');
    $new_last_name = trim($_POST['last_name'] ?? '');
    $current_pin = trim($_POST['current_pin'] ?? '');
    $new_pin = trim($_POST['new_pin'] ?? '');
    $confirm_pin = trim($_POST['confirm_pin'] ?? '');

    if (empty($new_username) || empty($new_first_name) || empty($new_last_name)) {
        $message = "❌ Username, first name, and last name cannot be empty.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Username is already taken.";
        } else {
            $stmt->close();

            if (!empty($current_pin) || !empty($new_pin) || !empty($confirm_pin)) {
                if (!password_verify($current_pin, $user['pin_code'])) {
                    $message = "❌ Current PIN is incorrect.";
                } elseif (!preg_match('/^\d{4}$/', $new_pin)) {
                    $message = "❌ New PIN must be exactly 4 digits.";
                } elseif ($new_pin !== $confirm_pin) {
                    $message = "❌ New PIN and confirmation do not match.";
                } else {
                    $new_hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, first_name = ?, middle_name = ?, last_name = ?, pin_code = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $new_username, $new_first_name, $new_middle_name, $new_last_name, $new_hashed_pin, $user_id);
                    if ($stmt->execute()) {
                        $message = "✅ PIN updated successfully.";
                        $_SESSION['username'] = $new_username;
                    } else {
                        $message = "❌ Error updating profile.";
                    }
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, first_name = ?, middle_name = ?, last_name = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $new_username, $new_first_name, $new_middle_name, $new_last_name, $user_id);
                if ($stmt->execute()) {
                    $message = "✅ Profile updated successfully.";
                    $_SESSION['username'] = $new_username;
                } else {
                    $message = "❌ Error updating profile.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>User Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Orbitron', sans-serif;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        margin: 0;
        padding: 20px;
        color: #f0f0f0;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background: #111;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 0 20px #00bfff55;
    }

    h1, h2 {
        text-align: center;
        color: #00bfff;
        margin-bottom: 30px;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
        color: #00bfff;
    }

    input[type="text"],
    input[type="password"],
    select {
        width: 100%;
        padding: 12px;
        margin-top: 5px;
        background: #1a1a1a;
        border: 1px solid #00bfff;
        border-radius: 8px;
        color: #fff;
        font-family: 'Orbitron', sans-serif;
    }

    input[readonly] {
        background: #333;
        cursor: not-allowed;
    }

    button {
        margin-top: 25px;
        width: 100%;
        background-color: #00bfff;
        color: #000;
        border: none;
        padding: 15px;
        font-size: 16px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #0088cc;
    }

    .logout {
        text-align: right;
        margin-bottom: 20px;
    }

    .logout a {
        color: #00bfff;
        text-decoration: none;
        font-weight: bold;
    }

    .logout a:hover {
        text-decoration: underline;
    }

    .message {
        margin-top: 15px;
        font-weight: bold;
        padding: 10px;
        border-radius: 8px;
    }

    .message.success {
        background: #0f0;
        color: #000;
    }

    .message:not(.success) {
        background: #f00;
        color: #fff;
    }

    .password-toggle {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle input {
        flex: 1;
    }

    .password-toggle .toggle-icon {
        position: absolute;
        right: 15px;
        cursor: pointer;
        font-size: 20px;
        color: #00bfff;
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        background: #1b1b1b;
        color: #fff;
    }

    th, td {
        padding: 12px;
        border: 1px solid #00bfff44;
        text-align: center;
    }

    th {
        background-color: #00bfff22;
    }

    select {
        background: #1a1a1a;
        color: #fff;
    }

    #username-status.available {
        color: #00ff99;
    }

    #username-status.taken {
        color: #ff4444;
    }

    hr {
        margin: 30px 0;
        border: 1px solid #00bfff44;
    }
</style>
</head>
<body>
<div class="container">
    <div class="logout">
        Logged in as <strong><?php echo htmlspecialchars($username); ?></strong> |
        <a href="logout.php">Logout</a>
    </div>
    <h1>User Profile</h1>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo strpos($message, '✅') === 0 ? 'success' : ''; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required />
        <span id="username-status"></span>

        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required />

        <label for="middle_name">Middle Name:</label>
        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>" />

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required />

        <label for="rfid_tag">RFID Tag (read-only):</label>
        <input type="text" id="rfid_tag" name="rfid_tag" value="<?php echo htmlspecialchars($user['rfid_tag']); ?>" readonly />

        <hr>

        <label>Change PIN (Optional):</label>

        <label for="current_pin">Current PIN:</label>
        <div class="password-toggle">
            <input type="password" id="current_pin" name="current_pin" pattern="\d{4}" maxlength="4" placeholder="Enter current 4-digit PIN" onkeypress="return onlyNumberKey(event)" />
            <span class="toggle-icon" onclick="togglePassword('current_pin')">&#128065;</span>
        </div>

        <label for="new_pin">New PIN:</label>
        <div class="password-toggle">
            <input type="password" id="new_pin" name="new_pin" pattern="\d{4}" maxlength="4" placeholder="Enter new 4-digit PIN" onkeypress="return onlyNumberKey(event)" />
            <span class="toggle-icon" onclick="togglePassword('new_pin')">&#128065;</span>
        </div>

        <label for="confirm_pin">Confirm New PIN:</label>
        <div class="password-toggle">
            <input type="password" id="confirm_pin" name="confirm_pin" pattern="\d{4}" maxlength="4" placeholder="Confirm new PIN" onkeypress="return onlyNumberKey(event)" />
            <span class="toggle-icon" onclick="togglePassword('confirm_pin')">&#128065;</span>
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

<?php if ($logs_result->num_rows > 0): ?>
<div class="container" style="margin-top: 40px;">
    <h2>Access History</h2>

    <form method="GET">
        <label for="method_filter">Filter by Method:</label>
        <select name="method_filter" id="method_filter" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="RFID" <?php echo ($method_filter === 'RFID') ? 'selected' : ''; ?>>RFID</option>
            <option value="PIN" <?php echo ($method_filter === 'PIN') ? 'selected' : ''; ?>>PIN</option>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th>Status</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while($log = $logs_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['method']); ?></td>
                    <td><?php echo htmlspecialchars($log['status']); ?></td>
                    <td><?php echo htmlspecialchars($log['datetime']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    input.type = input.type === "password" ? "text" : "password";
}

function onlyNumberKey(evt) {
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
}

document.getElementById('username').addEventListener('input', function () {
    const usernameInput = this;
    const username = usernameInput.value.trim();
    const statusSpan = document.getElementById('username-status');

    if (username.length < 3) {
        statusSpan.textContent = '';
        statusSpan.className = '';
        return;
    }

    fetch('check_username.php?username=' + encodeURIComponent(username))
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                statusSpan.textContent = '✅ Username is available';
                statusSpan.className = 'available';
            } else {
                statusSpan.innerHTML = `
                    ❌ Username is taken. Try <strong style="cursor:pointer; text-decoration:underline;" id="suggestion">${data.suggestion}</strong>
                `;
                statusSpan.className = 'taken';
                document.getElementById('suggestion').addEventListener('click', function() {
                    usernameInput.value = data.suggestion;
                    statusSpan.textContent = '✅ Username is available';
                    statusSpan.className = 'available';
                    usernameInput.dispatchEvent(new Event('input'));
                });
            }
        })
        .catch(err => console.error('Username check failed:', err));
});
</script>
</body>
</html>

