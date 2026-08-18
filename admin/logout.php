<?php
// admin/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();

// Clear persistent auth cookie
setcookie('brewpos_auth', '', time() - 3600, '/');

header("Location: login.php");
exit;
