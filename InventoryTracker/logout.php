<?php
session_start();
//destroys session and logs user out. They are redirected to the login page
$_SESSION = array();
session_destroy();
header("location: login.php");
exit;
?>

