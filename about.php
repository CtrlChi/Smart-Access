<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>About | Smart Door Lock</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap');

    * {
        box-sizing: border-box;
        font-family: 'Orbitron', sans-serif;
    }

    body {
        background: linear-gradient(to right, #0f1112, #203a43, #2c5364);
        margin: 3%;
        padding-top: 150px;
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
        font-size: 1.25rem;
        padding: 12px 18px;
        border-radius: 8px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    nav a:hover {
        background-color: #007acc;
        color: #ffffff;
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

    .container {
        max-width: 1000px;
        margin: auto;
        padding: 40px;
        background: #1e1e2f;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        animation: fadeIn 0.8s ease-out, pulseGlow 3s infinite ease-in-out;
        transition: transform 0.3s ease;
    }

    h1 {
        text-align: center;
        color: #00d4ff;
        margin-bottom: 20px;
        font-size: 2rem;
    }

    p {
        font-size: 1.1rem;
        color: #cccccc;
        line-height: 1.6;
        text-align: center;
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
        <img src="smartlogo3.png" alt="Trace College Logo">
    </div>
    <nav>
        <a href="login.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<div class="container">
    <h1>Smart Access</h1>
    <p>
        Smart Access provides secure connection using both RFID and a PIN-based authentication mechanism. 
        It’s designed for homes, offices, and restricted areas requiring safety and smart automation.
    </p>
</div>
<br>
<br>
<div class="container">
    <h1>Tech-driven Security</h1>
    <p>
        With real-time logging, user management, and seamless integration between hardware and web systems, 
        Smart Access door lock system showcases how technology can enhance modern security solutions.
    </p>
</div>
</body>
</html>
