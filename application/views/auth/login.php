<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login — InventoryApp</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f4f7fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .auth-card{width:100%;max-width:420px;background:#fff;padding:28px;border-radius:12px;box-shadow:0 10px 30px rgba(12,20,40,0.06)}
    .brand{font-weight:700;color:#0b61d8;margin-bottom:10px}
    .muted{color:#6b7280}
    .footer-note{font-size:13px;color:#94a3b8;margin-top:12px;text-align:center}
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="text-center mb-3">
      <div class="brand h4">InventoryApp</div>
      <div class="muted">Sign in to your account</div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= html_escape($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('auth/login'); ?>">
      <!-- adjust CSRF if your app uses it -->
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" required class="form-control" placeholder="username or email" value="<?= set_value('username'); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" required class="form-control" placeholder="your password">
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div><label class="form-check"><input type="checkbox" name="remember" class="form-check-input"> Remember me</label></div>
        <div><a href="<?= site_url('auth/register'); ?>">Create account</a></div>
      </div>

      <div class="d-grid">
        <button class="btn btn-primary" type="submit">Sign in</button>
      </div>
    </form>

    <div class="footer-note">
      By signing in you accept the app terms.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
