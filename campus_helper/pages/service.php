<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /campus_helper/'); exit; }

$db = getDB();
$stmt = $db->prepare("
    SELECT s.*, u.id as uid, u.username, u.full_name, u.university, u.bio,
           c.name as cat_name, c.icon as cat_icon,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM services s
    JOIN users u ON u.id = s.seller_id
    JOIN categories c ON c.id = s.category_id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.id = ? AND s.status='active'
    GROUP BY s.id
");
$stmt->execute([$id]);
$service = $stmt->fetch();
if (!$service) { header('Location: /campus_helper/'); exit; }

// Reviews
$reviews = $db->prepare("
    SELECT r.*, u.username, u.full_name
    FROM reviews r JOIN users u ON u.id = r.reviewer_id
    WHERE r.service_id = ? ORDER BY r.created_at DESC LIMIT 10
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$colors = ['linear-gradient(135deg,#667eea,#764ba2)','linear-gradient(135deg,#f093fb,#f5576c)','linear-gradient(135deg,#4facfe,#00f2fe)','linear-gradient(135deg,#43e97b,#38f9d7)','linear-gradient(135deg,#fa709a,#fee140)'];
$bg = $colors[$id % count($colors)];

$flash_success = flash('success');
$flash_error = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($service['title']) ?> — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--ink:#0e0e14;--cream:#f5f3ee;--accent:#ff5c35;--accent2:#3b82f6;--gold:#f59e0b;--surface:#fff;--muted:#6b7280;--border:#e5e7eb;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);}
a{text-decoration:none;color:inherit;}
nav{background:var(--ink);display:flex;align-items:center;gap:16px;padding:0 32px;height:64px;}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.3rem;color:#fff;}
.brand span{color:var(--accent);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:8px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:0.9rem;cursor:pointer;border:none;transition:all .2s;}
.btn-accent{background:var(--accent);color:#fff;}
.btn-accent:hover{background:#e04a26;}
.btn-primary{background:var(--accent2);color:#fff;}
.btn-primary:hover{background:#2563eb;}
.btn-ghost{background:transparent;color:rgba(255,255,255,.8);}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#fff;}
.btn-lg{padding:14px 32px;font-size:1rem;border-radius:10px;width:100%;justify-content:center;}
.flash{padding:12px 20px;border-radius:8px;margin:16px;font-size:.9rem;}
.flash-success{background:#d1fae5;color:#065f46;border-left:4px solid #10b981;}
.flash-error{background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444;}

.page-wrap{max-width:1100px;margin:0 auto;padding:32px 24px;display:grid;grid-template-columns:1fr 320px;gap:32px;}
@media(max-width:768px){.page-wrap{grid-template-columns:1fr;padding:16px;}}

.thumb{height:260px;border-radius:16px;margin-bottom:24px;display:flex;align-items:center;justify-content:center;font-size:4rem;background:<?= $bg ?>;}
.breadcrumb{font-size:.8rem;color:var(--muted);margin-bottom:16px;}
.breadcrumb a{color:var(--accent2);}
h1{font-family:'Syne',sans-serif;font-size:1.7rem;font-weight:700;line-height:1.3;margin-bottom:16px;}
.meta-row{display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.rating-badge{display:flex;align-items:center;gap:4px;font-weight:600;}
.star{color:var(--gold);}
.tag{background:var(--cream);border:1px solid var(--border);padding:4px 12px;border-radius:20px;font-size:.78rem;}
.section-title{font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem;margin-bottom:14px;margin-top:28px;}
.desc{color:#374151;line-height:1.7;font-size:.95rem;white-space:pre-line;}

/* Sidebar */
.sidebar-card{background:var(--surface);border-radius:14px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.08);position:sticky;top:80px;}
.price{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--ink);margin-bottom:4px;}
.delivery{color:var(--muted);font-size:.85rem;margin-bottom:20px;}
.seller-row{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.seller-av{width:44px;height:44px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;}
.seller-info small{color:var(--muted);font-size:.78rem;}
.seller-info strong{display:block;font-size:.9rem;}

/* Reviews */
.review-item{background:var(--surface);border-radius:10px;padding:16px;margin-bottom:12px;}
.review-header{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.reviewer-av{width:32px;height:32px;border-radius:50%;background:var(--accent2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;}
.review-stars{color:var(--gold);font-size:.9rem;}
.review-text{font-size:.88rem;color:#374151;line-height:1.6;}
.no-reviews{text-align:center;padding:32px;color:var(--muted);background:var(--surface);border-radius:10px;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:300;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:var(--surface);border-radius:16px;padding:32px;max-width:480px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.25);animation:slideUp .3s ease;}
@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-title{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:16px;}
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:.85rem;font-weight:500;margin-bottom:6px;}
.form-input{width:100%;padding:10px 14px;border:2px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:var(--accent2);}
textarea.form-input{resize:vertical;min-height:90px;}
.modal-close{float:right;background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);margin-top:-8px;}
</style>
</head>
<body>
<nav>
    <a href="/campus_helper/" class="brand">Campus<span>Helper</span></a>
    <div style="margin-left:auto;display:flex;gap:8px;">
    <?php if (isLoggedIn()): ?>
        <a href="/campus_helper/pages/dashboard.php" class="btn btn-ghost">Dashboard</a>
        <a href="/campus_helper/pages/logout.php" class="btn btn-ghost">Logout</a>
    <?php else: ?>
        <a href="/campus_helper/pages/login.php" class="btn btn-ghost">Login</a>
        <a href="/campus_helper/pages/register.php" class="btn btn-accent">Sign Up</a>
    <?php endif; ?>
    </div>
</nav>

<?php if ($flash_success): ?><div class="flash flash-success"><?= e($flash_success) ?></div><?php endif; ?>
<?php if ($flash_error): ?><div class="flash flash-error"><?= e($flash_error) ?></div><?php endif; ?>

<div class="page-wrap">
    <!-- LEFT -->
    <div>
        <div class="breadcrumb">
            <a href="/campus_helper/">Home</a> › <a href="/campus_helper/?q=<?= urlencode($service['cat_name']) ?>"><?= e($service['cat_icon'].' '.$service['cat_name']) ?></a>
        </div>
        <div class="thumb"><?= $service['cat_icon'] ?></div>
        <h1><?= e($service['title']) ?></h1>
        <div class="meta-row">
            <div class="rating-badge"><span class="star">★</span><?= number_format($service['avg_rating'],1) ?> <span style="color:var(--muted);font-weight:400">(<?= $service['review_count'] ?> reviews)</span></div>
            <span class="tag"><?= e($service['cat_icon'].' '.$service['cat_name']) ?></span>
            <span class="tag">📦 <?= $service['total_orders'] ?> orders</span>
            <span class="tag">⏱ <?= $service['delivery_days'] ?> day delivery</span>
        </div>

        <div class="section-title">About This Service</div>
        <div class="desc"><?= e($service['description']) ?></div>

        <!-- REVIEWS -->
        <div class="section-title">Reviews (<?= count($reviews) ?>)</div>
        <?php if (empty($reviews)): ?>
        <div class="no-reviews">🌟 No reviews yet. Be the first to order!</div>
        <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review-item">
            <div class="review-header">
                <div class="reviewer-av"><?= strtoupper(substr($r['username'],0,2)) ?></div>
                <div>
                    <strong style="font-size:.88rem"><?= e($r['full_name']) ?></strong>
                    <div class="review-stars"><?= str_repeat('★',$r['rating']).str_repeat('☆',5-$r['rating']) ?></div>
                </div>
                <div style="margin-left:auto;font-size:.75rem;color:var(--muted)"><?= date('M j, Y',strtotime($r['created_at'])) ?></div>
            </div>
            <div class="review-text"><?= e($r['comment']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <div>
        <div class="sidebar-card">
            <div class="seller-row">
                <div class="seller-av"><?= strtoupper(substr($service['username'],0,2)) ?></div>
                <div class="seller-info">
                    <strong><?= e($service['full_name']) ?></strong>
                    <small>@<?= e($service['username']) ?></small>
                    <?php if ($service['university']): ?><small><?= e($service['university']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="price"><?= formatMYR($service['price']) ?></div>
            <div class="delivery">Delivery in <?= $service['delivery_days'] ?> day(s)</div>

            <?php if (isLoggedIn() && currentUser()['id'] != $service['uid']): ?>
                <button class="btn btn-accent btn-lg" style="margin-bottom:10px;" onclick="document.getElementById('orderModal').classList.add('active')">
                    🛒 Order Now
                </button>
                <button class="btn btn-primary btn-lg" onclick="document.getElementById('msgModal').classList.add('active')">
                    💬 Contact Me
                </button>
            <?php elseif (!isLoggedIn()): ?>
                <a href="/campus_helper/pages/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-accent btn-lg" style="margin-bottom:10px;">
                    🛒 Order Now
                </a>
                <a href="/campus_helper/pages/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary btn-lg">
                    💬 Contact Me
                </a>
            <?php else: ?>
                <div style="background:#f5f3ee;border-radius:8px;padding:12px;text-align:center;font-size:.85rem;color:var(--muted)">
                    This is your service listing
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ORDER MODAL -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('orderModal').classList.remove('active')">✕</button>
        <div class="modal-title">🛒 Order This Service</div>
        <p style="color:var(--muted);font-size:.85rem;margin-bottom:16px;"><?= e($service['title']) ?> — <strong><?= formatMYR($service['price']) ?></strong></p>
        <form method="POST" action="/campus_helper/pages/order.php">
            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
            <div class="form-group">
                <label class="form-label">Requirements / Notes for Seller</label>
                <textarea name="requirements" class="form-input" placeholder="Describe what you need in detail..."></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;padding:12px">Proceed to Payment</button>
        </form>
    </div>
</div>

<!-- MESSAGE MODAL -->
<div class="modal-overlay" id="msgModal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('msgModal').classList.remove('active')">✕</button>
        <div class="modal-title">💬 Message Seller</div>
        <form method="POST" action="/campus_helper/pages/send_message.php">
            <input type="hidden" name="receiver_id" value="<?= $service['uid'] ?>">
            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
            <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI']) ?>">
            <div class="form-group">
                <label class="form-label">Your Message</label>
                <textarea name="content" class="form-input" placeholder="Hi! I'm interested in your service..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Send Message</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.modal-overlay').forEach(o=>{
    o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('active');});
});
</script>
</body>
</html>
