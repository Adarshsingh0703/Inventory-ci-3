<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:520px;margin:40px auto;padding:0 16px}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    label{display:block;margin:8px 0 4px}
    input{width:100%;padding:8px}
    small{color:#555}
    button{margin-top:12px;padding:8px 12px;cursor:pointer}
    a{color:#0b61d8;text-decoration:none}
  </style>
</head>
<body>
  <h2>Register</h2>

  <?php if (!empty($errors)): ?>
    <div class="msg err"><?= $errors; ?></div>
  <?php endif; ?>

  <form method="post" action="<?= site_url('auth/register'); ?>">
    <div>
      <label>Username</label>
      <input type="text" name="username" value="<?= set_value('username'); ?>" required>
      <small>3–50 chars; letters, numbers, dot, underscore, hyphen</small>
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" required>
      <small>8–12 chars; at least 1 uppercase &amp; 1 special character</small>
    </div>
    <div>
      <label>Display Name (optional)</label>
      <input type="text" name="display_name" value="<?= set_value('display_name'); ?>">
    </div>
    <button type="submit">Create Account</button>
  </form>

  <p style="margin-top:12px">
    Already have an account? <a href="<?= site_url('auth/login'); ?>">Login</a>
  </p>
</body>
</html>
