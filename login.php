<?php
session_start();

$conn = new mysqli("localhost:3306", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $pin_code = trim($_POST['pin'] ?? '');

    if (empty($username)) {
        $message = "❌ Please enter your username.";
        $message_type = "error";
    } elseif (!preg_match('/^\d{4}$/', $pin_code)) {
        $message = "❌ PIN must be exactly 4 digits.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, role, pin_code FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($pin_code, $user['pin_code'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['middle_name'] = $user['middle_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    header("Location: user_home.php");
                    exit;
                }
            } else {
                $message = "❌ Incorrect PIN.";
                $message_type = "error";
            }
        } else {
            $message = "❌ Username not found.";
            $message_type = "error";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&family=Inter:wght@400;600&display=swap');

    * {
        box-sizing: border-box;
        font-family: 'Orbitron', sans-serif;
    }

    h1, h2, h3, .card h2, label, button, nav a {
        font-family: 'Orbitron', sans-serif;
    }

    body {
        background: linear-gradient(to right, #0f1112, #203a43, #2c5364);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        padding-top: 80px;
        color: #f0f0f0;
    }

    body::before {
        content: "";
        background: url('2.png') no-repeat center center fixed;
        background-size: 130% 100%;
        position: fixed;
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 50px;
        z-index: 1000;
        color: #fff;
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
        border-radius: 10px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    nav a:hover {
        background-color: #007acc;
        color: #ffffff;
    }

    .card {
        background: #1e1e2f;
        padding: 50px 40px;
        border-radius: 24px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        width: 100%;
        max-width: 480px;
        animation: fadeIn 0.8s ease-out, pulseGlow 3s infinite ease-in-out;
        color: #f0f0f0;
    }

    .card h2 {
        margin-bottom: 25px;
        text-align: center;
        color: #00d4ff;
        font-size: 1.8rem;
    }

    label {
        display: block;
        margin: 15px 0 6px;
        font-weight: 600;
        color: #e0e0e0;
    }

    input {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #444;
        border-radius: 10px;
        background: #2a2a2a;
        color: #f0f0f0;
        transition: border-color 0.3s, box-shadow 0.2s;
        font-size: 1rem;
    }

    input:focus {
        border-color: #00ffff;
        box-shadow: 0 0 0 3px rgba(0, 255, 255, 0.2);
        outline: none;
    }

    button {
        margin-top: 25px;
        width: 100%;
        padding: 14px;
        background: #00d4ff;
        border: none;
        border-radius: 10px;
        color: #000;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s;
    }

    button:hover {
        background: #007acc;
        transform: translateY(-1px);
    }

    .note {
        margin-top: 20px;
        font-size: 0.9em;
        color: #cccccc;
        text-align: center;
        font-family: 'Inter', sans-serif;
    }

    .note a {
        color: #00d4ff;
        font-weight: 600;
        text-decoration: none;
    }

    .note a:hover {
        text-decoration: underline;
    }

    .message {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: bold;
    }

    .message.success {
        background-color: #144d3d;
        color: #a5d6a7;
        border: 1px solid #1b5e20;
    }

    .message.error {
        background-color: #4b0d0d;
        color: #ff8a80;
        border: 1px solid #b71c1c;
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

    @keyframes pulseGlow {
        0% {
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.1), 0 0 24px rgba(0, 255, 255, 0.05);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.2), 0 0 40px rgba(0, 255, 255, 0.1);
            transform: scale(1.01);
        }
        100% {
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.1), 0 0 24px rgba(0, 255, 255, 0.05);
            transform: scale(1);
        }
    }

    .toggle-pin {
        position: relative;
    }

    .toggle-pin span {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
        font-size: 1.2rem;
    }
</style>
</head>
<body>

<header>
    <div class="logo">
        <img src="smartlogo3.png" alt="Trace College Logo">
    </div>
    <nav>
        <a href="login.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
    
</header>

<div class="card">
    <h2>Smart Access Login</h2>

    <?php if (isset($message) && $message): ?>
        <div class="message <?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" maxlength="50" required autofocus autocomplete="username">

        <label for="pin">Enter your 4-digit PIN</label>
        <div class="toggle-pin" style="position: relative;">
            <input type="password" name="pin" id="pin" pattern="\d{4}" maxlength="4" required inputmode="numeric" autocomplete="off"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="padding-right: 30px;">
            <span onclick="togglePIN()" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;">
                👁️
            </span>
        </div>
        
        <button type="submit">Login</button>
    </form>

    <p class="note">
        Don't have an account yet? 
        <a href="register_user.php">Register here</a>
    </p>
</div>

<script>
function togglePIN() {
    const pinInput = document.getElementById('pin');
    const type = pinInput.getAttribute('type') === 'password' ? 'text' : 'password';
    pinInput.setAttribute('type', type);
}
</script>

</body>
</html>


