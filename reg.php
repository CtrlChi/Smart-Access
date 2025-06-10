<?php
if (isset($_GET['rfid'])) {
    header("Location: /doorlocked/register_user.php?rfid=" . urlencode($_GET['rfid']));
} else {
    echo "RFID not found.";
}
