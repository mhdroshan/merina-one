<?php
require 'auth.php';
logout();
header('Location: login.php');
exit();
?>