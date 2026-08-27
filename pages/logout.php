<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';

logout();
header('Location: ' . SITE_URL);
exit();
?>
