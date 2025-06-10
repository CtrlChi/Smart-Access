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
<title>User Home</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #74ebd5, #ACB6E5);
        margin: 0; padding: 20px;
    }
    .container {
        max-width: 450px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h1 {
        text-align: center;
        color: #333;
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
    }
    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 8px 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }
    input[readonly] {
        background: #f0f0f0;
        cursor: not-allowed;
    }
    button {
        margin-top: 20px;
        width: 100%;
        background-color: #4eb6a5;
        color: white;
        border: none;
        padding: 12px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover {
        background-color: #3a8a7b;
    }
    .message {
        margin-top: 15px;
        font-weight: bold;
        color: #d8000c;
    }
    .message.success {
        color: #4F8A10;
    }
    .logout {
        text-align: right;
        margin-bottom: 10px;
    }
    .logout a {
        color: #4eb6a5;
        text-decoration: none;
        font-weight: bold;
    }
    .logout a:hover {
        text-decoration: underline;
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
        right: 10px;
        cursor: pointer;
        user-select: none;
        font-size: 18px;
        color: #666;
        }
        .input-error {
        border: 2px solid #e74c3c;
        background-color: #fdecea;
    }
    #username-status {
        display: block;
        margin-top: 5px;
        font-weight: bold;
    }
    #username-status.available {
        color: #27ae60;
    }
    #username-status.taken {
        color: #e74c3c;
    }
        select, input[type="date"], input[type="text"], button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    button {
        background-color: #4eb6a5;
        color: white;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: #3c9e8c;
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
        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>"  />

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required />

        <label for="rfid_tag">RFID Tag (read-only):</label>
        <input type="text" id="rfid_tag" name="rfid_tag" value="<?php echo htmlspecialchars($user['rfid_tag']); ?>" readonly />

        <hr style="margin-top: 30px;">

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


        <script>
        function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
        }
        </script>

        <button type="submit">Update Profile</button>
    </form>
</div>
<script>
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
        .catch(err => {
            console.error('Error checking username:', err);
        });
});
</script>
<?php if ($logs_result->num_rows > 0): ?>
<div class="container" style="margin-top: 30px;">
    <h2 style="text-align:center;">Access History</h2>

    <form method="GET" style="margin-bottom: 20px;">
        <label for="method_filter">Method:</label>
        <select name="method_filter" id="method_filter" onchange="this.form.submit()" style="padding: 10px; width: 100%; border: 1px solid #ccc; border-radius: 5px;">
            <option value="">All</option>
            <option value="RFID" <?php echo (isset($_GET['method_filter']) && $_GET['method_filter'] === 'RFID') ? 'selected' : ''; ?>>RFID</option>
            <option value="PIN" <?php echo (isset($_GET['method_filter']) && $_GET['method_filter'] === 'PIN') ? 'selected' : ''; ?>>PIN</option>
        </select>
    </form>


    <?php if ($logs_result->num_rows > 0): ?>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ccc;">Method</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while($log = $logs_result->fetch_assoc()): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc;"><?php echo htmlspecialchars($log['method']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ccc;"><?php echo htmlspecialchars($log['status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ccc;"><?php echo htmlspecialchars($log['datetime']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; color:#777;">No logs found for the selected filters.</p>
    <?php endif; ?>
</div>

<?php endif; ?>

<script>
function onlyNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        evt.preventDefault();
        return false;
    }
    return true;
}
</script>

</body>
</html>
