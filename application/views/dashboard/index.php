<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--b:#0b61d8}
    body{font-family:sans-serif;max-width:1100px;margin:24px auto;padding:0 16px}
    a{color:var(--b);text-decoration:none}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:16px}
    .card{border:1px solid #ddd;border-radius:10px;padding:14px}
    .card h3{margin:0 0 6px}
    .muted{color:#555}
    .links a{display:inline-block;margin:6px 10px 0 0}
    .btn{display:inline-block;background:var(--b);color:#fff;padding:8px 12px;border-radius:8px}
    .btn-outline{display:inline-block;border:1px solid var(--b);color:var(--b);padding:8px 12px;border-radius:8px}
  </style>
</head>
<body>
  <div class="top">
    <h2>Welcome, <?= html_escape($display_name); ?></h2>
    <div>
      <a class="btn-outline" href="<?= site_url('auth/logout'); ?>">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Items</h3>
      <p class="muted">Manage inventory items without page reload (AJAX).</p>
      <div class="links">
        <a class="btn" href="<?= site_url('items'); ?>">Open Items</a>
        <a href="<?= site_url('items/create'); ?>">Add Item (classic form)</a>
      </div>
    </div>

    <div class="card">
      <h3>Categories</h3>
      <p class="muted">Create and organize categories (supports parent/child).</p>
      <div class="links">
        <a class="btn" href="<?= site_url('categories'); ?>">Open Categories</a>
        <a href="<?= site_url('categories/create'); ?>">Add Category (classic form)</a>
      </div>
    </div>

    <div class="card">
      <h3>Audit Logs</h3>
      <p class="muted">See who did what, and when — with before/after snapshots.</p>
      <div class="links">
        <a class="btn" href="<?= site_url('audit'); ?>">Open Audit Logs</a>
      </div>
    </div>

    <div class="card">
      <h3>API Endpoints</h3>
      <p class="muted">For testing JSON responses (must be logged in):</p>
      <div class="links">
        <a href="<?= site_url('api/items'); ?>" target="_blank" rel="noopener">/api/items</a>
        <a href="<?= site_url('api/categories'); ?>" target="_blank" rel="noopener">/api/categories</a>
        <a href="<?= site_url('api/audit'); ?>" target="_blank" rel="noopener">/api/audit</a>
      </div>
    </div>
  </div>
</body>
</html>
