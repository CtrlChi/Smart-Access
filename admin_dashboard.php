<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost:3307", "root", "", "doorlock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connsect_error);
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

$logs = $conn->query("
    SELECT logs.*, CONCAT(users.first_name, ' ', users.middle_name, ' ', users.last_name) AS full_name 
    FROM logs 
    LEFT JOIN users ON logs.user_id = users.id 
    ORDER BY logs.datetime DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
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
        padding: 0;
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

    header {
        background: #101820;
        color: #00bfff;
        padding: 0 50px;
        text-align: left;
        font-size: 24px;
        font-weight: bold;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 130px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

        .logo img {
        height: 120px;
        user-select: none;
    }


    .logout {
        font-size: 18px;
        color: #00bfff;
    }

    .logout a {
        color: #00bfff;
        text-decoration: none;
        font-weight: bold;
    }

    .logout a:hover {
        text-decoration: underline;
    }

    main {
        padding: 20px;
    }

    h2 {
        color: #00bfff;
        margin-top: 40px;
    }

    table, .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background: #1b1f22;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #333;
        color: #e0e0e0;
    }

    th {
        background: #0b2e44;
        color: #00bfff;
    }

    tr:hover {
        background: #2c3e50;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-weight: bold;
        text-align: center;
    }

    .alert.success {
        background-color: #143d2c;
        color: #9ff8cf;
        border: 1px solid #2ea88b;
    }

    .alert.error {
        background-color: #4c1c1c;
        color: #f78a8a;
        border: 1px solid #c24b4b;
    }

    .action-link {
        color: #00bfff;
        font-weight: bold;
        text-decoration: none;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background 0.3s ease;
    }

    .action-link:hover {
        background: #1b3d5d;
        text-decoration: underline;
    }

    .custom-table td {
        word-break: break-word;
        max-width: 200px;
        overflow-wrap: break-word;
    }
</style>
</head>
<body>
<header>
        <div class="logo">
        <img src="smartlogo3.png">
    </div>

    Admin Dashboard
    <div class="logout">
        Logged in as 
        <?php 
        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['middle_name'] . ' ' . $_SESSION['last_name']); 
        ?> | 
        <a href="logout.php">Logout</a>
    </div>
</header>
<main>
    <?php if (isset($_GET['message'])): ?>
        <?php $type = isset($_GET['type']) && $_GET['type'] === 'success' ? 'success' : 'error'; ?>
        <div class="alert <?php echo $type; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

<h2>Registered Users</h2>
<table class="custom-table">
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>RFID Tag</th>
        <th>PIN</th>
        <th>Role</th>
        <th>Actions</th> 
    </tr>
    <?php while ($row = $users->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
        <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
        <td><?php echo htmlspecialchars($row['rfid_tag']); ?></td>
        <td><?php echo htmlspecialchars($row['pin_code']); ?></td>
        <td><?php echo htmlspecialchars($row['role']); ?></td>
        <td>
            <a class="action-link" href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a> |
            <a class="action-link" href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h2>Access Logs</h2>
<table class="custom-table">
    <tr>
        <th>Log ID</th>
        <th>User</th>
        <th>Method</th>
        <th>Date/Time</th>
        <th>Status</th>
    </tr>
    <?php while ($log = $logs->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($log['id']); ?></td>
        <td><?php echo htmlspecialchars($log['full_name'] ?: 'Unknown'); ?></td>
        <td><?php echo htmlspecialchars($log['method']); ?></td>
        <td><?php echo htmlspecialchars($log['datetime']); ?></td>
        <td><?php echo htmlspecialchars($log['status']); ?></td>
    </tr>
    <?php endwhile; ?>
    </table>
</main>
    <script>
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.style.display = 'none';
        }, 4000); 
    </script>
</body>
</html>
<?php $conn->close(); ?>
