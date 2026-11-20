<?php
require __DIR__ . '/common.php';
$counts = [];
foreach (['Pets','Owners','Owns','Likes','Foods','Purchases'] as $name) {
    $counts[$name] = (int) $pdo->query('SELECT COUNT(*) FROM `' . $name . '`')->fetchColumn();
}
$nav = [
    'Pets' => ['pets.php', 'Manage pet records, ages, types and addresses.'],
    'Owners' => ['owners.php', 'Manage owner details and contact information.'],
    'Owns' => ['owns.php', 'Link pets to owners and record adoption details.'],
    'Foods' => ['foods.php', 'Manage food brands, types and prices.'],
    'Purchases' => ['purchases.php', 'Record purchases associated with owners and pets.']
];
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pet Admin | Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body{background:#f4f6fb;color:#263047;font-family:system-ui,-apple-system,sans-serif}
.navbar{background:#202a46!important}.navbar .navbar-brand,.navbar .nav-link{color:#fff!important}
.content{max-width:1120px;margin:0 auto;padding:32px 18px 70px}
.card{border:1px solid #e0e7f1;border-radius:13px;box-shadow:0 3px 14px #202a4609}
.stat{font-size:2.2rem;font-weight:750;color:#5365d9}.muted{color:#68758f}
.btn-primary{background:#5365d9;border-color:#5365d9}.btn-primary:hover{background:#4254c5}
</style></head><body>
<nav class="navbar navbar-expand-md navbar-dark"><div class="container-fluid" style="max-width:1120px">
<a class="navbar-brand fw-bold" href="index.php">🐾 Pet Admin</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
<?php foreach ($nav as $title => $link): ?><li class="nav-item"><a class="nav-link" href="<?=h($link[0])?>"><?=h($title)?></a></li><?php endforeach; ?>
</ul></div></div></nav>
<main class="content">
<div class="mb-4"><p class="text-primary fw-semibold mb-1">DATABASE MANAGEMENT</p><h1 class="display-6 fw-bold">Pet Management Dashboard</h1>
<p class="muted">View and update the existing pet database using the five CRUD pages below.</p></div>
<div class="row g-3 mb-4">
<?php foreach (['Pets','Owners','Owns','Foods','Purchases','Likes'] as $table): ?>
<div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="small muted"><?=h($table)?></div><div class="stat"><?= $counts[$table] ?></div><div class="small muted">record(s)</div></div></div></div>
<?php endforeach; ?></div>
<div class="row g-3">
<?php foreach ($nav as $title => $link): ?><div class="col-12 col-md-6 col-xl-4"><div class="card h-100"><div class="card-body d-flex flex-column">
<h2 class="h5 fw-bold"><?=h($title)?></h2><p class="muted flex-grow-1"><?=h($link[1])?></p>
<a class="btn btn-primary align-self-start" href="<?=h($link[0])?>">Open <?=h($title)?></a>
</div></div></div><?php endforeach; ?></div>
<div class="card mt-4"><div class="card-body"><h2 class="h5 fw-bold">How this application works</h2>
<p class="mb-0">Browser form (HTML/CSS/JavaScript/Bootstrap) → HTTP POST → PHP handler (<code>show_page()</code> in <code>common.php</code>) → server validation → PDO prepared SQL statement → MySQL <code>PetDatabase</code> → HTTP 303 redirect → refreshed records table.</p>
</div></div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
