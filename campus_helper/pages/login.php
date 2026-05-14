<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (isLoggedIn()) { header('Location: /campus_helper/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $redirect = $_GET['redirect'] ?? '/campus_helper/';
            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email/username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f5f3ee;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.auth-wrap{width:90%;max-width:440px;}
.auth-card{background:#fff;border-radius:16px;padding:40px;box-shadow:0 8px 40px rgba(0,0,0,0.1);}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;text-align:center;margin-bottom:28px;color:#0e0e14;}
.brand span{color:#ff5c35;}
h1{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700;margin-bottom:6px;}
.subtitle{color:#6b7280;font-size:0.9rem;margin-bottom:28px;}
.form-group{margin-bottom:18px;}
.form-label{display:block;font-size:0.85rem;font-weight:500;margin-bottom:6px;}
.form-input{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:#3b82f6;}
.btn{display:block;width:100%;padding:12px;border-radius:8px;border:none;background:#ff5c35;color:#fff;font-family:'DM Sans',sans-serif;font-weight:600;font-size:1rem;cursor:pointer;transition:background .2s;text-align:center;}
.btn:hover{background:#e04a26;}
.error{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:0.85rem;margin-bottom:18px;border-left:4px solid #ef4444;}
.link-row{text-align:center;margin-top:20px;font-size:0.88rem;color:#6b7280;}
.link-row a{color:#3b82f6;font-weight:500;}
.divider{text-align:center;color:#9ca3af;font-size:0.8rem;margin:16px 0;}
.demo-box{background:#f5f3ee;border-radius:8px;padding:12px 14px;font-size:0.8rem;color:#374151;margin-bottom:18px;}
.demo-box strong{display:block;margin-bottom:4px;color:#0e0e14;}
</style>
</head>
<body>
<div class="auth-wrap">
    <a href="/campus_helper/" class="brand" style="display:block;text-decoration:none">Campus<span>Helper</span></a>
    <div class="auth-card">
        <h1>Welcome back 👋</h1>
        <p class="subtitle">Login to access your campus services</p>
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <div class="demo-box">
            <strong>🧪 Demo Login Credentials</strong>
            Email: <code>ali@university.edu</code> &nbsp;|&nbsp; Password: <code>password</code>
        </div>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email or Username</label>
                <input type="text" name="email" class="form-input" placeholder="you@university.edu" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="link-row">Don't have an account? <a href="/campus_helper/pages/register.php">Sign up free</a></div>
    </div>
</div>
</body>
</html>
