<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$db = getDB();
$user = currentUser();
$uid = $user['id'];

// Tabs
$tab = $_GET['tab'] ?? 'buying';

// Buying orders
$buying = $db->prepare("
    SELECT o.*, s.title, u.full_name as seller_name, u.username as seller_username,
           r.id as review_id
    FROM orders o JOIN services s ON s.id=o.service_id
    JOIN users u ON u.id=o.seller_id
    LEFT JOIN reviews r ON r.order_id=o.id
    WHERE o.buyer_id=? ORDER BY o.created_at DESC
");
$buying->execute([$uid]);
$buying = $buying->fetchAll();

// Selling orders
$selling = $db->prepare("
    SELECT o.*, s.title, u.full_name as buyer_name
    FROM orders o JOIN services s ON s.id=o.service_id
    JOIN users u ON u.id=o.buyer_id
    WHERE o.seller_id=? ORDER BY o.created_at DESC
");
$selling->execute([$uid]);
$selling = $selling->fetchAll();

// My services
$services = $db->prepare("SELECT s.*, c.name as cat_name, COALESCE(AVG(r.rating),0) as avg_rating FROM services s JOIN categories c ON c.id=s.category_id LEFT JOIN reviews r ON r.service_id=s.id WHERE s.seller_id=? AND s.status!='deleted' GROUP BY s.id ORDER BY s.id DESC");
$services->execute([$uid]);
$services = $services->fetchAll();

// Messages inbox
$messages = $db->prepare("
    SELECT m.*, u.full_name as sender_name, u.username as sender_username,
           s.title as service_title
    FROM messages m JOIN users u ON u.id=m.sender_id
    LEFT JOIN services s ON s.id=m.service_id
    WHERE m.receiver_id=? ORDER BY m.created_at DESC LIMIT 20
");
$messages->execute([$uid]);
$messages = $messages->fetchAll();

// Mark reading orders complete
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['complete_order'])) {
    $oid = (int)$_POST['complete_order'];
    $db->prepare("UPDATE orders SET status='completed' WHERE id=? AND seller_id=?")->execute([$oid,$uid]);
    header('Location: /campus_helper/pages/dashboard.php?tab=selling');
    exit;
}

// Submit review
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['review_order_id'])) {
    $oid = (int)$_POST['review_order_id'];
    $rating = max(1,min(5,(int)$_POST['rating']));
    $comment = trim($_POST['comment']);
    // Verify order belongs to buyer and is completed
    $chk = $db->prepare("SELECT * FROM orders WHERE id=? AND buyer_id=? AND status='completed'");
    $chk->execute([$oid,$uid]);
    $ord = $chk->fetch();
    if ($ord) {
        try {
            $db->prepare("INSERT INTO reviews (order_id,reviewer_id,seller_id,service_id,rating,comment) VALUES(?,?,?,?,?,?)")
               ->execute([$oid,$uid,$ord['seller_id'],$ord['service_id'],$rating,$comment]);
            flash('success','Review submitted!');
        } catch(Exception $e) { flash('error','Review already submitted.'); }
    }
    header('Location: /campus_helper/pages/dashboard.php?tab=buying');
    exit;
}

$flash_success = flash('success');
$flash_error = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--ink:#0e0e14;--cream:#f5f3ee;--accent:#ff5c35;--accent2:#3b82f6;--gold:#f59e0b;--surface:#fff;--muted:#6b7280;--border:#e5e7eb;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);}
a{text-decoration:none;color:inherit;}
nav{background:var(--ink);display:flex;align-items:center;gap:16px;padding:0 32px;height:64px;}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.3rem;color:#fff;}
.brand span{color:var(--accent);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-family:'DM Sans',sans-serif;font-weight:500;font-size:.875rem;cursor:pointer;border:none;transition:all .2s;}
.btn-ghost{background:transparent;color:rgba(255,255,255,.8);}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#fff;}
.btn-accent{background:var(--accent);color:#fff;}
.btn-accent:hover{background:#e04a26;}
.btn-primary{background:var(--accent2);color:#fff;}
.btn-sm{padding:5px 12px;font-size:.78rem;}
.flash{padding:12px 20px;border-radius:8px;margin:16px 32px;font-size:.9rem;}
.flash-success{background:#d1fae5;color:#065f46;border-left:4px solid #10b981;}
.flash-error{background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444;}

.wrap{max-width:1100px;margin:0 auto;padding:32px 24px;}
.page-header{margin-bottom:28px;}
.page-header h1{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:700;}
.page-header p{color:var(--muted);margin-top:4px;}

.tabs{display:flex;gap:4px;background:var(--surface);border-radius:10px;padding:4px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,.06);width:fit-content;}
.tab{padding:8px 20px;border-radius:7px;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;color:var(--muted);}
.tab.active{background:var(--ink);color:#fff;}
.tab:hover:not(.active){color:var(--ink);}

.card{background:var(--surface);border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px;}
.card-body{padding:20px;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;padding:14px 16px;border-bottom:2px solid var(--border);}
td{padding:14px 16px;border-bottom:1px solid var(--border);font-size:.88rem;vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafafa;}

.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;}
.badge-pending{background:#fef3c7;color:#92400e;}
.badge-paid,.badge-in_progress{background:#dbeafe;color:#1e40af;}
.badge-completed{background:#d1fae5;color:#065f46;}
.badge-cancelled{background:#fee2e2;color:#991b1b;}
.badge-disputed{background:#ede9fe;color:#5b21b6;}

.service-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;}
.svc-card{background:var(--surface);border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;flex-direction:column;gap:10px;}
.svc-title{font-weight:600;font-size:.9rem;line-height:1.4;}
.svc-meta{display:flex;align-items:center;justify-content:space-between;}
.svc-actions{display:flex;gap:8px;}

.msg-item{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px;align-items:flex-start;}
.msg-item:last-child{border:none;}
.msg-av{width:36px;height:36px;border-radius:50%;background:var(--accent2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.msg-content{flex:1;}
.msg-sender{font-weight:600;font-size:.88rem;}
.msg-text{color:var(--muted);font-size:.83rem;margin-top:2px;}
.msg-time{font-size:.75rem;color:var(--muted);flex-shrink:0;}

.empty{text-align:center;padding:40px;color:var(--muted);}
.empty .icon{font-size:2.5rem;margin-bottom:12px;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:300;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:var(--surface);border-radius:16px;padding:32px;max-width:440px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.25);animation:slideUp .3s ease;}
@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-title{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:16px;}
.modal-close{float:right;background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);margin-top:-8px;}
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:.85rem;font-weight:500;margin-bottom:6px;}
.form-input{width:100%;padding:10px 14px;border:2px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:var(--accent2);}
.stars-input{display:flex;gap:4px;margin-bottom:14px;font-size:1.6rem;cursor:pointer;}
.star-btn{cursor:pointer;color:#d1d5db;transition:color .1s;}
.star-btn.lit{color:var(--gold);}
</style>
</head>
<body>
<nav>
    <a href="/campus_helper/" class="brand">Campus<span>Helper</span></a>
    <div style="margin-left:auto;display:flex;gap:8px;">
        <a href="/campus_helper/" class="btn btn-ghost">Browse</a>
        <a href="/campus_helper/pages/create_service.php" class="btn btn-accent">+ List Service</a>
        <a href="/campus_helper/pages/logout.php" class="btn btn-ghost">Logout</a>
    </div>
</nav>

<?php if ($flash_success): ?><div class="flash flash-success"><?= e($flash_success) ?></div><?php endif; ?>
<?php if ($flash_error): ?><div class="flash flash-error"><?= e($flash_error) ?></div><?php endif; ?>

<div class="wrap">
    <div class="page-header">
        <h1>👋 Hello, <?= e($user['full_name']) ?></h1>
        <p>@<?= e($user['username']) ?> <?= $user['university'] ? '· '.$user['university'] : '' ?></p>
    </div>

    <div class="tabs">
        <?php foreach([['buying','🛒 My Purchases'],['selling','📦 My Sales'],['services','📋 My Listings'],['messages','💬 Messages']] as [$t,$label]): ?>
        <a href="?tab=<?= $t ?>" class="tab <?= $tab===$t?'active':'' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($tab==='buying'): ?>
    <div class="card">
        <?php if (empty($buying)): ?>
        <div class="empty"><div class="icon">🛍️</div><p>No purchases yet. <a href="/campus_helper/" style="color:var(--accent2)">Browse services</a> to get started.</p></div>
        <?php else: ?>
        <table>
            <thead><tr><th>Service</th><th>Seller</th><th>Amount</th><th>Status</th><th>Delivery</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($buying as $o): ?>
            <tr>
                <td><?= e($o['title']) ?></td>
                <td><?= e($o['seller_name']) ?></td>
                <td><?= formatMYR($o['amount']) ?></td>
                <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
                <td><?= $o['delivery_date'] ? date('M j, Y',strtotime($o['delivery_date'])) : '—' ?></td>
                <td>
                <?php if ($o['status']==='completed' && !$o['review_id']): ?>
                    <button class="btn btn-primary btn-sm" onclick="openReview(<?= $o['id'] ?>)">⭐ Review</button>
                <?php elseif ($o['status']==='completed'): ?>
                    <span style="color:var(--muted);font-size:.78rem">Reviewed ✓</span>
                <?php elseif ($o['status']==='pending'): ?>
                    <a href="/campus_helper/pages/order.php?confirm=1&order_id=<?= $o['id'] ?>" class="btn btn-accent btn-sm">Pay Now</a>
                <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php elseif ($tab==='selling'): ?>
    <div class="card">
        <?php if (empty($selling)): ?>
        <div class="empty"><div class="icon">📦</div><p>No sales yet. <a href="/campus_helper/pages/create_service.php" style="color:var(--accent2)">List a service</a> to start earning.</p></div>
        <?php else: ?>
        <table>
            <thead><tr><th>Service</th><th>Buyer</th><th>Earnings</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($selling as $o): ?>
            <tr>
                <td><?= e($o['title']) ?></td>
                <td><?= e($o['buyer_name']) ?></td>
                <td><?= formatMYR($o['seller_earnings']) ?></td>
                <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
                <td>
                <?php if ($o['status']==='in_progress'): ?>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Mark this order as completed?')">
                        <input type="hidden" name="complete_order" value="<?= $o['id'] ?>">
                        <button class="btn btn-accent btn-sm">✓ Mark Complete</button>
                    </form>
                <?php else: ?><span style="color:var(--muted);font-size:.78rem"><?= ucfirst($o['status']) ?></span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php elseif ($tab==='services'): ?>
    <div style="margin-bottom:16px"><a href="/campus_helper/pages/create_service.php" class="btn btn-accent">+ Create New Listing</a></div>
    <?php if (empty($services)): ?>
    <div class="card"><div class="empty"><div class="icon">📋</div><p>No listings yet. Create your first service!</p></div></div>
    <?php else: ?>
    <div class="service-grid">
        <?php foreach ($services as $s): ?>
        <div class="svc-card">
            <div class="svc-title"><?= e($s['title']) ?></div>
            <div class="svc-meta">
                <span style="font-family:'Syne',sans-serif;font-weight:700"><?= formatMYR($s['price']) ?></span>
                <span style="color:var(--muted);font-size:.8rem">⭐ <?= number_format($s['avg_rating'],1) ?></span>
            </div>
            <div style="font-size:.78rem;color:var(--muted)"><?= e($s['cat_name']) ?> · <?= $s['total_orders'] ?> orders</div>
            <div class="svc-actions">
                <a href="/campus_helper/pages/service.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">View</a>
                <a href="/campus_helper/pages/create_service.php?edit=<?= $s['id'] ?>" class="btn btn-sm" style="background:#f5f3ee;color:var(--ink)">Edit</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($tab==='messages'): ?>
    <div class="card">
        <?php if (empty($messages)): ?>
        <div class="empty"><div class="icon">💬</div><p>No messages yet.</p></div>
        <?php else: ?>
        <?php foreach ($messages as $m): ?>
        <div class="msg-item">
            <div class="msg-av"><?= strtoupper(substr($m['sender_username'],0,2)) ?></div>
            <div class="msg-content">
                <div class="msg-sender"><?= e($m['sender_name']) ?> <small style="color:var(--muted);font-weight:400">· re: <?= e($m['service_title'] ?? 'General') ?></small></div>
                <div class="msg-text"><?= e($m['content']) ?></div>
            </div>
            <div class="msg-time"><?= date('M j',strtotime($m['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- REVIEW MODAL -->
<div class="modal-overlay" id="reviewModal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('reviewModal').classList.remove('active')">✕</button>
        <div class="modal-title">⭐ Leave a Review</div>
        <form method="POST" id="reviewForm">
            <input type="hidden" name="review_order_id" id="reviewOrderId">
            <input type="hidden" name="rating" id="ratingInput" value="5">
            <div class="stars-input" id="starsInput">
                <?php for($i=1;$i<=5;$i++): ?>
                <span class="star-btn lit" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</span>
                <?php endfor; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Your Comment</label>
                <textarea name="comment" class="form-input" placeholder="Share your experience..." style="min-height:80px"></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;padding:12px">Submit Review</button>
        </form>
    </div>
</div>

<script>
function openReview(orderId){
    document.getElementById('reviewOrderId').value = orderId;
    document.getElementById('reviewModal').classList.add('active');
}
function setRating(val){
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-btn').forEach((s,i)=>{
        s.classList.toggle('lit', i < val);
    });
}
document.getElementById('reviewModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});
</script>
</body>
</html>
