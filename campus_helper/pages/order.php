<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$db = getDB();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['confirm'])) {
    // Create pending order
    $service_id = (int)($_POST['service_id'] ?? 0);
    $requirements = trim($_POST['requirements'] ?? '');

    $svc = $db->prepare("SELECT * FROM services WHERE id=? AND status='active'");
    $svc->execute([$service_id]);
    $svc = $svc->fetch();

    if (!$svc) { flash('error','Service not found.'); header('Location: /campus_helper/'); exit; }
    if ($svc['seller_id'] == $user['id']) { flash('error','You cannot order your own service.'); header('Location: /campus_helper/pages/service.php?id='.$service_id); exit; }

    $fee = round($svc['price'] * 0.05, 2);
    $earnings = $svc['price'] - $fee;

    $stmt = $db->prepare("INSERT INTO orders (service_id,buyer_id,seller_id,amount,platform_fee,seller_earnings,requirements,delivery_date,status) VALUES(?,?,?,?,?,?,?,?,?)");
    $delivery = date('Y-m-d', strtotime('+'.$svc['delivery_days'].' days'));
    $stmt->execute([$service_id, $user['id'], $svc['seller_id'], $svc['price'], $fee, $earnings, $requirements, $delivery, 'pending']);
    $order_id = $db->lastInsertId();
    header('Location: /campus_helper/pages/order.php?confirm=1&order_id='.$order_id);
    exit;
}

// Show payment page
$order_id = (int)($_GET['order_id'] ?? 0);
$order = $db->prepare("SELECT o.*,s.title,s.delivery_days,u.full_name as seller_name,u.username as seller_username FROM orders o JOIN services s ON s.id=o.service_id JOIN users u ON u.id=o.seller_id WHERE o.id=? AND o.buyer_id=?");
$order->execute([$order_id, $user['id']]);
$order = $order->fetch();
if (!$order) { flash('error','Order not found.'); header('Location: /campus_helper/'); exit; }

// Handle payment confirmation
if (isset($_GET['pay'])) {
    if ($order['status'] !== 'pending') { flash('error','Order already processed.'); header('Location: /campus_helper/pages/dashboard.php'); exit; }
    // Simulate payment
    $ref = 'CH-' . strtoupper(uniqid());
    $db->prepare("INSERT INTO payments (order_id,payer_id,amount,transaction_ref,status,paid_at) VALUES(?,?,?,?,'success',NOW())")->execute([$order_id,$user['id'],$order['amount'],$ref]);
    $db->prepare("UPDATE orders SET status='in_progress' WHERE id=?")->execute([$order_id]);
    $db->prepare("UPDATE services SET total_orders=total_orders+1 WHERE id=?")->execute([$order['service_id']]);
    flash('success','Payment successful! Your order is now in progress. Reference: '.$ref);
    header('Location: /campus_helper/pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment — CampusHelper</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f5f3ee;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px;}
.card{background:#fff;border-radius:16px;padding:36px;max-width:520px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.1);}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.2rem;color:#0e0e14;margin-bottom:24px;display:block;text-decoration:none;}
.brand span{color:#ff5c35;}
h1{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:700;margin-bottom:6px;}
.subtitle{color:#6b7280;font-size:.9rem;margin-bottom:24px;}
.order-box{background:#f5f3ee;border-radius:10px;padding:16px;margin-bottom:24px;}
.order-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e5e7eb;font-size:.9rem;}
.order-row:last-child{border:none;font-weight:700;font-size:1rem;}
.total-row{background:#0e0e14;color:#fff;border-radius:8px;padding:14px 16px;display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;margin-bottom:20px;}
.total-row span{color:#ff5c35;}
.payment-methods{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.pm{border:2px solid #e5e7eb;border-radius:10px;padding:14px;text-align:center;cursor:pointer;transition:all .2s;}
.pm.active,.pm:hover{border-color:#3b82f6;background:#eff6ff;}
.pm .pm-icon{font-size:1.6rem;margin-bottom:6px;}
.pm .pm-name{font-size:.8rem;font-weight:500;}
.btn{display:block;width:100%;padding:14px;border-radius:10px;border:none;background:#ff5c35;color:#fff;font-family:'DM Sans',sans-serif;font-weight:700;font-size:1rem;cursor:pointer;transition:background .2s;text-align:center;text-decoration:none;}
.btn:hover{background:#e04a26;}
.btn-back{background:transparent;color:#6b7280;border:2px solid #e5e7eb;margin-top:10px;}
.btn-back:hover{border-color:#6b7280;background:#f5f3ee;}
.secure{display:flex;align-items:center;justify-content:center;gap:6px;color:#6b7280;font-size:.8rem;margin-top:14px;}
</style>
</head>
<body>
<div class="card">
    <a href="/campus_helper/" class="brand">Campus<span>Helper</span></a>
    <h1>💳 Complete Payment</h1>
    <p class="subtitle">Review your order before paying</p>

    <div class="order-box">
        <div class="order-row"><span>Service</span><span style="max-width:220px;text-align:right;font-size:.85rem"><?= e($order['title']) ?></span></div>
        <div class="order-row"><span>Seller</span><span><?= e($order['seller_name']) ?></span></div>
        <div class="order-row"><span>Delivery by</span><span><?= date('M j, Y',strtotime($order['delivery_date'])) ?></span></div>
        <div class="order-row"><span>Service fee (5%)</span><span>+ <?= formatMYR($order['platform_fee']) ?></span></div>
        <div class="order-row"><span>Total</span><span style="color:#ff5c35"><?= formatMYR($order['amount']) ?></span></div>
    </div>

    <div class="total-row">Total to Pay <span><?= formatMYR($order['amount']) ?></span></div>

    <p style="font-size:.85rem;font-weight:500;margin-bottom:12px;">Select Payment Method</p>
    <div class="payment-methods">
        <?php foreach([['💳','Credit/Debit Card'],['🏦','Online Banking'],['📱','Touch \'n Go'],['💰','CampusHelper Credits']] as [$icon,$name]): ?>
        <div class="pm" onclick="selectPM(this)">
            <div class="pm-icon"><?= $icon ?></div>
            <div class="pm-name"><?= $name ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <a href="/campus_helper/pages/order.php?confirm=1&order_id=<?= $order_id ?>&pay=1" class="btn" id="payBtn" onclick="return confirm('Confirm payment of <?= formatMYR($order['amount']) ?>?')">
        🔒 Pay <?= formatMYR($order['amount']) ?>
    </a>
    <a href="/campus_helper/" class="btn btn-back">← Cancel</a>
    <div class="secure">🔒 Secured by CampusHelper Payment Gateway</div>
</div>
<script>
function selectPM(el) {
    document.querySelectorAll('.pm').forEach(e=>e.classList.remove('active'));
    el.classList.add('active');
}
document.querySelector('.pm').classList.add('active');
</script>
</body>
</html>
