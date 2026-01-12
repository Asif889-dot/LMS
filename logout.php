<?php
require_once 'User.php';

$user = new User();
$user->logout();
redirect('index.php');
?>
