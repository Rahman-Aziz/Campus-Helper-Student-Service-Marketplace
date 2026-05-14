<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (isLoggedIn()) { header('Location: /campus_helper/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!$full_name || !$username || !$email || !$password) {
        $error = 'All required fields must be filled in.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            $error = 'Email or username already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username,email,password,full_name,university) VALUES(?,?,?,?,?)");
            $stmt->execute([$username, $email, $hash, $full_name, $university]);
            $id = $db->lastInsertId();
            $user = $db->prepare("SELECT * FROM users WHERE id=?");
            $user->execute([$id]);
            $user = $user->fetch();
            $_SESSION['user_id'] = $id;
            $_SESSION['user'] = $user;
            flash('success', 'Welcome to CampusHelper, ' . $full_name . '!');
            header('Location: /campus_helper/');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign Up — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f5f3ee;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 0;}
.auth-wrap{width:90%;max-width:480px;}
.auth-card{background:#fff;border-radius:16px;padding:40px;box-shadow:0 8px 40px rgba(0,0,0,0.1);}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;text-align:center;margin-bottom:28px;color:#0e0e14;display:block;text-decoration:none;}
.brand span{color:#ff5c35;}
h1{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;margin-bottom:6px;}
.subtitle{color:#6b7280;font-size:0.9rem;margin-bottom:28px;}
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:0.85rem;font-weight:500;margin-bottom:6px;}
.form-input{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:#3b82f6;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.btn{display:block;width:100%;padding:12px;border-radius:8px;border:none;background:#ff5c35;color:#fff;font-family:'DM Sans',sans-serif;font-weight:600;font-size:1rem;cursor:pointer;transition:background .2s;text-align:center;}
.btn:hover{background:#e04a26;}
.error{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:0.85rem;margin-bottom:18px;border-left:4px solid #ef4444;}
.link-row{text-align:center;margin-top:20px;font-size:0.88rem;color:#6b7280;}
.link-row a{color:#3b82f6;font-weight:500;}
</style>
</head>
<body>
<div class="auth-wrap">
    <a href="/campus_helper/" class="brand">Campus<span>Helper</span></a>
    <div class="auth-card">
        <h1>Join CampusHelper 🎓</h1>
        <p class="subtitle">Create your free account to buy or sell services</p>
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-input" placeholder="Ahmad Syafiq" value="<?= e($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div class="row2">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-input" placeholder="ahmad123" value="<?= e($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">University</label>
                    <input type="text" name="university" class="form-input" placeholder="UTM, UM, UPM..." value="<?= e($_POST['university'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" placeholder="you@university.edu" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row2">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-input" placeholder="Min 6 chars" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm" class="form-input" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" class="btn">Create Account</button>
        </form>
        <div class="link-row">Already have an account? <a href="/campus_helper/pages/login.php">Login</a></div>
    </div>
</div>
</body>
</html>
