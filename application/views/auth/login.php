<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:420px;margin:40px auto;padding:0 16px}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    label{display:block;margin:8px 0 4px}
    input{width:100%;padding:8px}
    button{margin-top:12px;padding:8px 12px;cursor:pointer}
    a{color:#0b61d8;text-decoration:none}
  </style>
</head>
<body>
  <h2>Login</h2>

  <?php if (!empty($errors)): ?>
    <div class="msg err"><?= $errors; ?></div>
  <?php endif; ?>

  <form method="post" action="<?= site_url('auth/login'); ?>">
    <div>
      <label>Username</label>
      <input type="text" name="username" value="<?= set_value('username'); ?>" required>
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit">Log In</button>
  </form>

  <p style="margin-top:12px">
    No account? <a href="<?= site_url('auth/register'); ?>">Register</a>
  </p>
</body>
</html>
