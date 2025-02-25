<?php
session_start();
$_SESSION['test'] = "Session is working!";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
