<?php
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    file_put_contents("latest_uid.txt", $uid);
    echo $uid;
}
?>
