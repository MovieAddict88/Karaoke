<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /admin/index.php');
    } elseif ($_SESSION['role'] === 'reseller') {
        header('Location: /reseller/index.php');
    } else {
        header('Location: /admin/login.php');
    }
} else {
    header('Location: /admin/login.php');
}
exit;
