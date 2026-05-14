<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$db = getDB();
$user = currentUser();

$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$service_id  = (int)($_POST['service_id'] ?? 0) ?: null;
$content     = trim($_POST['content'] ?? '');
$redirect    = $_POST['redirect'] ?? '/campus_helper/pages/dashboard.php?tab=messages';

if ($content && $receiver_id && $receiver_id !== $user['id']) {
    $db->prepare("INSERT INTO messages (sender_id,receiver_id,service_id,content) VALUES(?,?,?,?)")
       ->execute([$user['id'], $receiver_id, $service_id, $content]);
    flash('success', 'Message sent successfully!');
} else {
    flash('error', 'Could not send message.');
}

header('Location: ' . $redirect);
exit;
