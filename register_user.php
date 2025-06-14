<?php
session_start();

$conn = new mysqli("localhost:3306", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateUniqueId($conn) {
    do {
        $id = rand(1000, 9999); 
        $result = $conn->query("SELECT id FROM users WHERE id = $id");
    } while ($result->num_rows > 0);
    return $id;
}


$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    $full_name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;

    $pin_code = trim($_POST['pin'] ?? '');

    if (!preg_match('/^\d{4}$/', $pin_code)) {
        $message = "❌ PIN must be exactly 4 digits.";
        $message_type = "error";
    } else {
        $result = $conn->query("SELECT uid FROM pending_rfid ORDER BY scanned_at DESC LIMIT 1");
        $rfid_tag = '';
        if ($result && $row = $result->fetch_assoc()) {
            $rfid_tag = $row['uid'];
        }

        if (empty($full_name) || empty($pin_code)) {
            $message = "❌ Please fill in all required fields.";
            $message_type = "error";
        } elseif (empty($rfid_tag)) {
            $message = "❌ No RFID scanned yet. Please scan your RFID card first.";
            $message_type = "error";
        } else {
            $hashed_pin = password_hash($pin_code, PASSWORD_DEFAULT);

            $user_id = generateUniqueId($conn);

            $stmt = $conn->prepare("INSERT INTO users (id, first_name, middle_name, last_name, pin_code, rfid_tag, username) VALUES (?, ?, ?, ?, ?, ?, ?)");


            if ($stmt) {
                $base_username = strtolower($first_name . '.' . $last_name);
                $username = $base_username;
                $counter = 1;

                while (true) {
                    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $check_stmt->bind_param("s", $username);
                    $check_stmt->execute();
                    $check_stmt->store_result();

                    if ($check_stmt->num_rows === 0) {
                        $check_stmt->close();
                        break;
                    }

                    $check_stmt->close();
                    $username = $base_username . $counter;
                    $counter++;
                }

                $stmt->bind_param("issssss", $user_id, $first_name, $middle_name, $last_name, $hashed_pin, $rfid_tag, $username);

                if ($stmt->execute()) {

                    $del_stmt = $conn->prepare("DELETE FROM pending_rfid WHERE uid = ?");
                    $del_stmt->bind_param("s", $rfid_tag);
                    $del_stmt->execute();
                    $del_stmt->close();

                    $message = "✅ Registration successful! Your User ID: " . htmlspecialchars($user_id) . "Your Username: " . htmlspecialchars($username) . "Your RFID UID: " . htmlspecialchars($rfid_tag);
                    $message_type = "success";

                    header("refresh:8;url=login.php");
                } else {
                    $message = "❌ Error during registration: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "❌ Prepare statement failed: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>User Registration</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap');

    * {
        box-sizing: border-box;
        font-family: 'Orbitron', sans-serif;
    }

    body {
        background: linear-gradient(to right, #0f1112, #203a43, #2c5364);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        color: #f0f0f0;
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
        opacity: 0.15;

    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 130px;
        background: #1e1e2f;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 50px;
        z-index: 1000;
    }

    .logo img {
        height: 120px;
        user-select: none;
    }

    nav {
        display: flex;
        gap: 30px;
    }

    nav a {
        color: #00ffff;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 10px 16px;
        border-radius: 8px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    nav a:hover {
        background-color: #007acc;
        color: #fff;
    }

    .card {
        background: #1e1e2f;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 500px;
        margin-top: 200px;
        animation: fadeIn 0.8s ease-out;
    }

    .card h2 {
        margin-bottom: 10px;
        text-align: center;
        color: #00d4ff;
        font-size: 1.8rem;
    }

    label {
        display: block;
        margin: 15px 0 5px;
        font-weight: 600;
        color: #ccc;
    }

    input {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #444;
        border-radius: 8px;
        background: #0f1214;
        color: #eee;
        transition: border-color 0.3s, box-shadow 0.2s;
        font-size: 1rem;
    }

    input:focus {
        border-color: #00d4ff;
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.2);
        outline: none;
    }

    button {
        margin-top: 20px;
        width: 100%;
        padding: 12px;
        background: #007acc;
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s;
    }

    button:hover {
        background: #00aaff;
        transform: translateY(-1px);
    }

    .note {
        margin-top: 15px;
        font-size: 0.85em;
        color: #aaa;
        text-align: center;
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: bold;
    }

    .message.success {
        background-color: #1f4037;
        color: #a8ff78;
        border: 1px solid #00ff99;
    }

    .message.error {
        background-color: #3c1f1f;
        color: #ff6b6b;
        border: 1px solid #ff4d4d;
    }

    @keyframes fadeIn {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>
</head>
<body>

<header>
        <div class="logo">
        <img src="smartlogo3.png">
    </div>
    <nav>
        <a href="login.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<div class="card">
    <h2>Smart Access User Registration</h2>

    <?php if ($message): ?>
        <div class="message <?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Middle Name (optional)</label>
        <input type="text" name="middle_name">

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>4-digit PIN</label>
        <div style="position: relative;">
            <input type="password" name="pin" id="pinInput" inputmode="numeric" pattern="\d{4}" maxlength="4" required
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                style="padding-right: 40px;">
            <span onclick="togglePIN()" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;">
                👁️
            </span>
        </div>

        <p><small>Make sure to scan your RFID card first to register.</small></p>

        <button type="submit">Register</button>
    </form>
    <p class="note">
        Already have an account? 
        <a href="login.php" style="color:  #4eb6a5; font-weight: bold; text-decoration: none;">Login here</a>
    </p>
</div>

<script>
function togglePIN() {
    const pinInput = document.getElementById('pinInput');
    const type = pinInput.getAttribute('type') === 'password' ? 'text' : 'password';
    pinInput.setAttribute('type', type);
}
</script>

</body>
</html>