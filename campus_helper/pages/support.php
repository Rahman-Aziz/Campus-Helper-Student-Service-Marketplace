<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $email   = trim($_POST['email'] ?? '');
    $type    = $_POST['type'] ?? 'general';
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Help Request');
    $user_id = isLoggedIn() ? currentUser()['id'] : null;

    if ($message && $email) {
        $db->prepare("INSERT INTO support_tickets (user_id,subject,message,type) VALUES(?,?,?,?)")
           ->execute([$user_id, $subject, $message, $type]);
        flash('success', '✅ Your support request has been received! Our team will respond within 24 hours.');
    } else {
        flash('error', 'Please fill in all fields.');
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '/campus_helper/';
header('Location: ' . $redirect);
exit;
