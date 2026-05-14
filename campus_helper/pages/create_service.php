<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$db = getDB();
$user = currentUser();
$uid = $user['id'];
$categories = $db->query("SELECT * FROM categories ORDER BY id")->fetchAll();

$edit_id = (int)($_GET['edit'] ?? 0);
$existing = null;
if ($edit_id) {
    $s = $db->prepare("SELECT * FROM services WHERE id=? AND seller_id=?");
    $s->execute([$edit_id, $uid]);
    $existing = $s->fetch();
    if (!$existing) { header('Location: /campus_helper/pages/dashboard.php?tab=services'); exit; }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $delivery_days = max(1,(int)($_POST['delivery_days'] ?? 3));

    if (!$title || !$description || !$category_id || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($existing) {
            $db->prepare("UPDATE services SET title=?,description=?,category_id=?,price=?,delivery_days=? WHERE id=? AND seller_id=?")
               ->execute([$title,$description,$category_id,$price,$delivery_days,$edit_id,$uid]);
            flash('success','Service updated successfully!');
        } else {
            $db->prepare("INSERT INTO services (seller_id,category_id,title,description,price,delivery_days) VALUES(?,?,?,?,?,?)")
               ->execute([$uid,$category_id,$title,$description,$price,$delivery_days]);
            flash('success','Service listed successfully!');
        }
        header('Location: /campus_helper/pages/dashboard.php?tab=services');
        exit;
    }
}
$f = $existing ?? $_POST;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $existing?'Edit':'Create' ?> Service — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--ink:#0e0e14;--cream:#f5f3ee;--accent:#ff5c35;--accent2:#3b82f6;--surface:#fff;--muted:#6b7280;--border:#e5e7eb;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);}
a{text-decoration:none;color:inherit;}
nav{background:var(--ink);display:flex;align-items:center;gap:16px;padding:0 32px;height:64px;}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.3rem;color:#fff;}
.brand span{color:var(--accent);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-family:'DM Sans',sans-serif;font-weight:500;font-size:.875rem;cursor:pointer;border:none;transition:all .2s;}
.btn-ghost{background:transparent;color:rgba(255,255,255,.8);}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#fff;}

.wrap{max-width:720px;margin:40px auto;padding:0 24px;}
.card{background:var(--surface);border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.08);}
h1{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:6px;}
.subtitle{color:var(--muted);margin-bottom:28px;font-size:.9rem;}
.form-group{margin-bottom:20px;}
.form-label{display:block;font-size:.85rem;font-weight:600;margin-bottom:6px;color:var(--ink);}
.form-label small{font-weight:400;color:var(--muted);}
.form-input{width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border .2s;color:var(--ink);}
.form-input:focus{border-color:var(--accent2);}
textarea.form-input{resize:vertical;min-height:130px;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.btn-submit{background:var(--accent);color:#fff;padding:13px 28px;font-size:1rem;font-weight:700;border-radius:10px;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s;}
.btn-submit:hover{background:#e04a26;}
.btn-back-link{color:var(--accent2);font-size:.88rem;margin-left:16px;}
.error{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:.85rem;margin-bottom:18px;border-left:4px solid #ef4444;}
.cat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:6px;}
.cat-opt{border:2px solid var(--border);border-radius:8px;padding:10px 8px;text-align:center;cursor:pointer;transition:all .2s;font-size:.78rem;}
.cat-opt:hover,.cat-opt.selected{border-color:var(--accent);background:#fff5f2;}
.cat-opt .icon{font-size:1.3rem;margin-bottom:4px;}
@media(max-width:480px){.row2{grid-template-columns:1fr;}.cat-grid{grid-template-columns:repeat(2,1fr);}}
</style>
</head>
<body>
<nav>
    <a href="/campus_helper/" class="brand">Campus<span>Helper</span></a>
    <div style="margin-left:auto">
        <a href="/campus_helper/pages/dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>
</nav>
<div class="wrap">
    <div class="card">
        <h1><?= $existing ? '✏️ Edit Service' : '🚀 List a New Service' ?></h1>
        <p class="subtitle"><?= $existing ? 'Update your service details.' : 'Share your skills and start earning from fellow students.' ?></p>
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Category *</label>
                <div class="cat-grid">
                    <?php foreach ($categories as $c): ?>
                    <div class="cat-opt <?= (($f['category_id'] ?? 0)==$c['id'])?'selected':'' ?>" onclick="selectCat(<?= $c['id'] ?>,this)">
                        <div class="icon"><?= $c['icon'] ?></div>
                        <div><?= e($c['name']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="category_id" id="category_id" value="<?= $f['category_id'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Service Title * <small>(e.g. "I will write your essay in 24 hours")</small></label>
                <input type="text" name="title" class="form-input" placeholder="I will ..." value="<?= e($f['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-input" placeholder="Describe what you offer, your experience, what the buyer will receive..." required><?= e($f['description'] ?? '') ?></textarea>
            </div>
            <div class="row2">
                <div class="form-group">
                    <label class="form-label">Price (RM) *</label>
                    <input type="number" name="price" class="form-input" placeholder="30.00" min="5" step="0.50" value="<?= e($f['price'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Days *</label>
                    <input type="number" name="delivery_days" class="form-input" placeholder="3" min="1" max="30" value="<?= e($f['delivery_days'] ?? 3) ?>" required>
                </div>
            </div>
            <button type="submit" class="btn-submit"><?= $existing ? 'Update Service' : '✓ Publish Service' ?></button>
            <a href="/campus_helper/pages/dashboard.php?tab=services" class="btn-back-link">Cancel</a>
        </form>
    </div>
</div>
<script>
function selectCat(id, el) {
    document.querySelectorAll('.cat-opt').forEach(e=>e.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('category_id').value = id;
}
</script>
</body>
</html>
