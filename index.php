<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY id")->fetchAll();

// Fetch featured services with seller info and avg rating
$services = $db->query("
    SELECT s.*, u.username, u.full_name, u.university,
           c.name as cat_name,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM services s
    JOIN users u ON u.id = s.seller_id
    JOIN categories c ON c.id = s.category_id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.status='active'
    GROUP BY s.id
    ORDER BY s.total_orders DESC, s.id DESC
    LIMIT 8
")->fetchAll();

// Search
$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
if ($searchQuery) {
    $stmt = $db->prepare("
        SELECT s.*, u.username, u.full_name, c.name as cat_name,
               COALESCE(AVG(r.rating),0) as avg_rating, COUNT(r.id) as review_count
        FROM services s
        JOIN users u ON u.id = s.seller_id
        JOIN categories c ON c.id = s.category_id
        LEFT JOIN reviews r ON r.service_id = s.id
        WHERE s.status='active' AND (s.title LIKE ? OR s.description LIKE ? OR c.name LIKE ?)
        GROUP BY s.id ORDER BY avg_rating DESC LIMIT 20
    ");
    $like = "%$searchQuery%";
    $stmt->execute([$like, $like, $like]);
    $searchResults = $stmt->fetchAll();
}

$flash_success = flash('success');
$flash_error = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CampusHelper — Student Services Marketplace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
:root {
    --ink: #0e0e14;
    --cream: #f5f3ee;
    --accent: #ff5c35;
    --accent2: #3b82f6;
    --gold: #f59e0b;
    --surface: #ffffff;
    --muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 4px 24px rgba(0,0,0,0.08);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--ink); min-height: 100vh; }
a { text-decoration: none; color: inherit; }

/* NAV */
nav {
    background: var(--ink);
    position: sticky; top: 0; z-index: 100;
    display: flex; align-items: center; gap: 16px;
    padding: 0 32px; height: 64px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.25);
}
.nav-brand { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.3rem; color: #fff; letter-spacing: -0.5px; }
.nav-brand span { color: var(--accent); }
.nav-search { flex: 1; max-width: 480px; position: relative; }
.nav-search input {
    width: 100%; padding: 8px 16px 8px 40px;
    border-radius: 8px; border: none;
    background: rgba(255,255,255,0.1); color: #fff;
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    outline: none; transition: background 0.2s;
}
.nav-search input::placeholder { color: rgba(255,255,255,0.45); }
.nav-search input:focus { background: rgba(255,255,255,0.18); }
.nav-search .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.5); font-size: 1rem; }
.nav-links { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-weight: 500; font-size: 0.875rem; cursor: pointer; border: none; transition: all 0.2s; }
.btn-ghost { background: transparent; color: rgba(255,255,255,0.8); }
.btn-ghost:hover { background: rgba(255,255,255,0.1); color: #fff; }
.btn-accent { background: var(--accent); color: #fff; }
.btn-accent:hover { background: #e04a26; transform: translateY(-1px); }
.btn-primary { background: var(--accent2); color: #fff; }
.btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
.btn-outline { background: transparent; color: var(--ink); border: 2px solid var(--border); }
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }
.btn-sm { padding: 6px 14px; font-size: 0.8rem; }
.btn-lg { padding: 12px 28px; font-size: 1rem; border-radius: 10px; }
.btn-full { width: 100%; justify-content: center; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover { background: #dc2626; }

/* FLASH */
.flash { padding: 12px 20px; border-radius: 8px; margin: 16px 32px; font-size: 0.9rem; }
.flash-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.flash-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }

/* HERO */
.hero {
    background: linear-gradient(135deg, var(--ink) 0%, #1a1a2e 60%, #16213e 100%);
    color: #fff; padding: 80px 32px 60px;
    position: relative; overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 70% 50%, rgba(255,92,53,0.15) 0%, transparent 60%);
}
.hero-inner { max-width: 1100px; margin: 0 auto; position: relative; }
.hero h1 { font-family: 'Syne', sans-serif; font-size: clamp(2rem,5vw,3.5rem); font-weight: 800; line-height: 1.1; margin-bottom: 16px; }
.hero h1 .highlight { color: var(--accent); }
.hero p { color: rgba(255,255,255,0.7); font-size: 1.1rem; max-width: 520px; margin-bottom: 32px; }
.hero-search {
    display: flex; gap: 0; max-width: 560px;
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.hero-search input {
    flex: 1; padding: 14px 20px; border: none; outline: none;
    font-family: 'DM Sans', sans-serif; font-size: 1rem; color: var(--ink);
}
.hero-search button {
    background: var(--accent); color: #fff; border: none;
    padding: 14px 24px; cursor: pointer; font-weight: 600;
    font-family: 'DM Sans', sans-serif; font-size: 0.95rem;
    transition: background 0.2s;
}
.hero-search button:hover { background: #e04a26; }
.hero-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 20px; }
.hero-tag { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.75); padding: 5px 14px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: all 0.2s; border: 1px solid rgba(255,255,255,0.15); }
.hero-tag:hover { background: rgba(255,255,255,0.2); color: #fff; }

/* SECTION */
.section { padding: 56px 32px; max-width: 1140px; margin: 0 auto; }
.section-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 32px; }
.section-title { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 700; }
.section-link { color: var(--accent); font-size: 0.875rem; font-weight: 500; }
.section-link:hover { text-decoration: underline; }

/* CATEGORIES */
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
.category-card {
    background: var(--surface); border-radius: var(--radius); padding: 20px 12px;
    text-align: center; cursor: pointer; transition: all 0.2s;
    border: 2px solid transparent; box-shadow: var(--shadow);
}
.category-card:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 8px 24px rgba(255,92,53,0.15); }
.category-card .icon { font-size: 1.8rem; margin-bottom: 8px; }
.category-card .name { font-size: 0.78rem; font-weight: 500; color: var(--ink); }

/* SERVICE CARDS */
.services-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
.service-card {
    background: var(--surface); border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow); transition: all 0.25s; cursor: pointer;
    display: flex; flex-direction: column;
}
.service-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
.service-thumb {
    height: 160px; background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: rgba(255,255,255,0.8);
    position: relative;
}
.service-thumb .cat-badge {
    position: absolute; top: 10px; left: 10px;
    background: rgba(0,0,0,0.5); color: #fff; padding: 3px 10px;
    border-radius: 20px; font-size: 0.7rem;
}
.service-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.service-seller { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.seller-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--accent); color: #fff; display: flex; align-items: center;
    justify-content: center; font-size: 0.7rem; font-weight: 700;
}
.seller-name { font-size: 0.8rem; color: var(--muted); }
.service-title { font-weight: 600; font-size: 0.9rem; line-height: 1.4; margin-bottom: 10px; flex: 1; }
.service-meta { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
.service-rating { display: flex; align-items: center; gap: 4px; font-size: 0.8rem; color: var(--muted); }
.star { color: var(--gold); }
.service-price { font-family: 'Syne', sans-serif; font-weight: 700; color: var(--ink); font-size: 0.95rem; }

/* USER BADGE */
.user-badge { display: flex; align-items: center; gap: 8px; }
.user-avatar-nav { width: 32px; height: 32px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }

/* HELP BUTTON */
.help-float {
    position: fixed; bottom: 28px; right: 28px; z-index: 200;
    background: var(--accent2); color: #fff;
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; cursor: pointer; box-shadow: 0 4px 20px rgba(59,130,246,0.5);
    border: none; transition: all 0.2s;
}
.help-float:hover { transform: scale(1.1); background: #2563eb; }

/* FOOTER */
footer {
    background: var(--ink); color: rgba(255,255,255,0.6);
    padding: 40px 32px 24px;
    margin-top: 40px;
}
.footer-inner { max-width: 1140px; margin: 0 auto; }
.footer-brand { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.2rem; color: #fff; margin-bottom: 8px; }
.footer-brand span { color: var(--accent); }
.footer-note { font-size: 0.8rem; margin-top: 24px; }

/* MODAL */
.modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    z-index: 300; align-items: center; justify-content: center;
}

.help-tickets {
    position: fixed;
    bottom: 90px; /* sits just above help button */
    right: 28px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #0e0e14;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    transition: transform 0.2s ease;
}

.help-tickets:hover {
    transform: scale(1.08);
}
.modal-overlay.active { display: flex; }
.modal {
    background: var(--surface); border-radius: 16px; padding: 32px;
    max-width: 480px; width: 90%; box-shadow: 0 24px 64px rgba(0,0,0,0.25);
    animation: slideUp 0.3s ease;
}
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-title { font-family: 'Syne', sans-serif; font-size: 1.3rem; font-weight: 700; margin-bottom: 20px; }
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 6px; color: var(--ink); }
.form-input {
    width: 100%; padding: 10px 14px; border: 2px solid var(--border);
    border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    color: var(--ink); outline: none; transition: border 0.2s;
}
.form-input:focus { border-color: var(--accent2); }
textarea.form-input { resize: vertical; min-height: 100px; }
.modal-close { float: right; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--muted); margin-top: -8px; }

/* Search results */
.search-banner { background: var(--surface); border-bottom: 2px solid var(--border); padding: 16px 32px; }
.search-banner h2 { font-family: 'Syne', sans-serif; font-size: 1.2rem; }
.search-banner h2 span { color: var(--accent); }

/* Responsive */
@media(max-width:768px){
    nav { padding: 0 16px; }
    .nav-search { max-width: none; flex: 1; }
    .hero { padding: 48px 16px 40px; }
    .section { padding: 40px 16px; }
    .services-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
    .categories-grid { grid-template-columns: repeat(4,1fr); }
}
@media(max-width:480px){
    .services-grid { grid-template-columns: 1fr; }
    .categories-grid { grid-template-columns: repeat(2,1fr); }
}
</style>
</head>
<body>

<!-- NAVIGATION -->
<nav>
    <a href="./" class="nav-brand">Campus<span>Helper</span></a>
    <div class="nav-search">
        <span class="search-icon">🔍</span>
        <form method="GET" action="./">
            <input type="text" name="q" placeholder="Search services..." value="<?= e($searchQuery) ?>">
        </form>
    </div>
    <div class="nav-links">
        <?php if (isLoggedIn()): $u = currentUser(); ?>
            <a href="pages/dashboard.php" class="btn btn-ghost">Dashboard</a>
            <a href="pages/create_service.php" class="btn btn-accent btn-sm">+ List Service</a>
            <div class="user-badge">
                <div class="user-avatar-nav"><?= strtoupper(substr($u['username'],0,2)) ?></div>
            </div>
            <a href="/pages/support_ticket.php" class="btn btn-ghost">
                Tickets
            </a>
            <a href="pages/logout.php" class="btn btn-ghost btn-sm">Logout</a>
        <?php else: ?>
            <a href="pages/login.php" class="btn btn-ghost">Login</a>
            <a href="pages/register.php" class="btn btn-accent">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($flash_success): ?>
<div class="flash flash-success"><?= e($flash_success) ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="flash flash-error"><?= e($flash_error) ?></div>
<?php endif; ?>

<?php if ($searchQuery): ?>
<!-- SEARCH RESULTS -->
<div class="search-banner">
    <h2>Results for "<span><?= e($searchQuery) ?></span>" — <?= count($searchResults) ?> found</h2>
</div>
<div class="section">
    <?php if (empty($searchResults)): ?>
        <p style="color:var(--muted); text-align:center; padding:40px">No services found. Try a different keyword.</p>
    <?php else: ?>
    <div class="services-grid">
        <?php foreach ($searchResults as $s): ?>
        <a href="pages/service.php?id=<?= $s['id'] ?>" class="service-card">
            <div class="service-thumb" style="background:<?= ['linear-gradient(135deg,#667eea,#764ba2)','linear-gradient(135deg,#f093fb,#f5576c)','linear-gradient(135deg,#4facfe,#00f2fe)','linear-gradient(135deg,#43e97b,#38f9d7)'][($s['id']-1)%4] ?>">
                <span class="cat-badge"><?= e($s['cat_name']) ?></span>
            </div>
            <div class="service-body">
                <div class="service-seller">
                    <div class="seller-avatar"><?= strtoupper(substr($s['username'],0,2)) ?></div>
                    <span class="seller-name"><?= e($s['full_name']) ?></span>
                </div>
                <div class="service-title"><?= e($s['title']) ?></div>
                <div class="service-meta">
                    <div class="service-rating"><span class="star">★</span><?= number_format($s['avg_rating'],1) ?> (<?= $s['review_count'] ?>)</div>
                    <div class="service-price"><?= formatMYR($s['price']) ?></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- HERO -->
<div class="hero">
    <div class="hero-inner">
        <h1>Find Student Services<br>You Can <span class="highlight">Trust</span></h1>
        <p>Your campus marketplace — get help from fellow students for assignments, coding, design, tutoring & more.</p>
        <form method="GET" action="./">
            <div class="hero-search">
                <input type="text" name="q" placeholder='Try "essay writing" or "python help"...'>
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="hero-tags">
            <?php foreach ($categories as $c): ?>
            <a href="?q=<?= urlencode($c['name']) ?>" class="hero-tag"><?= $c['icon'] ?> <?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- CATEGORIES -->
<div class="section">
    <div class="section-header">
        <div class="section-title">Browse by Category</div>
    </div>
    <div class="categories-grid">
        <?php foreach ($categories as $c): ?>
        <a href="?q=<?= urlencode($c['name']) ?>" class="category-card">
            <div class="icon"><?= $c['icon'] ?></div>
            <div class="name"><?= e($c['name']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- FEATURED SERVICES -->
<div class="section" style="padding-top:0">
    <div class="section-header">
        <div class="section-title">Featured Services</div>
    </div>
    <div class="services-grid">
        <?php
        $colors = ['linear-gradient(135deg,#667eea,#764ba2)','linear-gradient(135deg,#f093fb,#f5576c)','linear-gradient(135deg,#4facfe,#00f2fe)','linear-gradient(135deg,#43e97b,#38f9d7)','linear-gradient(135deg,#fa709a,#fee140)','linear-gradient(135deg,#30cfd0,#667eea)','linear-gradient(135deg,#a18cd1,#fbc2eb)','linear-gradient(135deg,#fccb90,#d57eeb)'];
        foreach ($services as $i => $s): ?>
        <a href="pages/service.php?id=<?= $s['id'] ?>" class="service-card">
            <div class="service-thumb" style="background:<?= $colors[$i % count($colors)] ?>">
                <span class="cat-badge"><?= e($s['cat_name']) ?></span>
            </div>
            <div class="service-body">
                <div class="service-seller">
                    <div class="seller-avatar"><?= strtoupper(substr($s['username'],0,2)) ?></div>
                    <span class="seller-name"><?= e($s['full_name']) ?></span>
                </div>
                <div class="service-title"><?= e($s['title']) ?></div>
                <div class="service-meta">
                    <div class="service-rating"><span class="star">★</span><?= number_format($s['avg_rating'],1) ?> (<?= $s['review_count'] ?>)</div>
                    <div class="service-price"><?= formatMYR($s['price']) ?></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- HOW IT WORKS -->
<div style="background:var(--ink); color:#fff; padding:56px 32px; margin-top:16px;">
<div style="max-width:1140px; margin:0 auto;">
    <h2 style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:40px;">How It Works</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;text-align:center;">
        <?php foreach([['🔍','Find a Service','Browse or search for what you need'],['💬','Contact Seller','Message the seller to discuss details'],['💳','Pay Securely','Pay via our secure payment system'],['⭐','Get & Review','Receive your service and leave a review']] as $i=>[$icon,$title,$desc]): ?>
        <div>
            <div style="font-size:2.5rem;margin-bottom:12px;"><?= $icon ?></div>
            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;margin-bottom:8px;"><?= $title ?></div>
            <div style="color:rgba(255,255,255,0.6);font-size:0.85rem;"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</div>
<?php endif; ?>

<!-- HELP BUTTON -->
<button class="help-float" title="Get Help" onclick="document.getElementById('helpModal').classList.add('active')">❓</button>

<!-- HELP MODAL -->
<div class="modal-overlay" id="helpModal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('helpModal').classList.remove('active')">✕</button>
        <div class="modal-title">🛟 How can we help?</div>
        <p style="color:var(--muted);font-size:0.9rem;margin-bottom:20px;">Connect with our student support team or report an issue.</p>
        <form method="POST" action="pages/support.php">
            <div class="form-group">
                <label class="form-label">Your Email</label>
                <input type="email" name="email" class="form-input" placeholder="student@university.edu" <?= isLoggedIn() ? 'value="'.e(currentUser()['email']).'"' : '' ?>>
            </div>
            <div class="form-group">
                <label class="form-label">Issue Type</label>
                <select name="type" class="form-input">
                    <option value="general">General Question</option>
                    <option value="scam_report">Report Scam / Fraud</option>
                    <option value="payment_issue">Payment Issue</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-input" placeholder="Describe your issue..." required></textarea>
            </div>
            <input type="hidden" name="subject" value="Help Request from CampusHelper">
            <button type="submit" class="btn btn-primary btn-full btn-lg">Send to Support</button>
            <a href="pages/support_ticket.php" class="help-tickets" title="Active Tickets">🎫Active Tickets</a>
        </form>
    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-brand">Campus<span>Helper</span></div>
        <p style="font-size:0.85rem;max-width:400px;margin-top:8px;">A student-to-student services marketplace. Find help or earn money sharing your skills on campus.</p>
        <div class="footer-note">© <?= date('Y') ?> CampusHelper. For students, by students.</div>
    </div>
</footer>

<script>
// Close modal on overlay click
document.getElementById('helpModal').addEventListener('click', function(e){
    if(e.target===this) this.classList.remove('active');
});
// Hero tag search
document.querySelectorAll('.hero-tag').forEach(tag => {
    tag.addEventListener('click', e => {
        e.preventDefault();
        const q = tag.textContent.trim().replace(/^[^\w]+/,'');
        window.location.href = '?q=' + encodeURIComponent(q);
    });
});
</script>
</body>
</html>
