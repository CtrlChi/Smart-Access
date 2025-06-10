<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Contact | Smart Door Lock</title>
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

.container {
    max-width: 600px;
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
    text-align: center;
    font-size: 1.1rem;
    color: #cccccc;
    margin-bottom: 30px;
    line-height: 1.6;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input, textarea {
    padding: 10px;
    border-radius: 8px;
    border: 2px solid #004d4d;
    font-size: 1rem;
    resize: none;
    background: #0f1112;
    color: #00ffff;
}

input::placeholder,
textarea::placeholder {
    color: #007c99;
}

button {
    padding: 12px;
    background: #00ffff;
    border: none;
    border-radius: 8px;
    color: #0f1112;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease;
}

button:hover {
    background: #007acc;
    color: #fff;
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

<div class="container">
    <h1>Contact Us</h1>
    <p>Have questions or feedback? Reach out below:</p>
    <br>
    <p>ccoronel@tracecollege.edu.ph</p>
    <p>cdave@tracecollege.edu.ph</p>
    <p>aosaki@tracecollege.edu.ph</p>
    <p>ksotosanchez@tracecollege.edu.ph</p>

    <form method="post" action="#">
        <input type="te
