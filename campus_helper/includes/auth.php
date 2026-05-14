<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /campus_helper/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function flash($key, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
    } else {
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function formatMYR($amount) {
    return 'RM ' . number_format($amount, 2);
}
