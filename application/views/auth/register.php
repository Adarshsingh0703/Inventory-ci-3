<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register — InventoryApp</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600,700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f4f7fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .auth-card{width:100%;max-width:520px;background:#fff;padding:28px;border-radius:12px;box-shadow:0 10px 30px rgba(12,20,40,0.06)}
    .brand{font-weight:700;color:#0b61d8;margin-bottom:10px}
    .muted{color:#6b7280}
    .footer-note{font-size:13px;color:#94a3b8;margin-top:12px;text-align:center}
    .form-label { font-weight:600; }
    .strength-bar { height:8px; border-radius:6px; background:#e9eefb; overflow:hidden; }
    .strength-fill { height:100%; width:0; transition:width .18s ease, background .18s ease; background:transparent; }
    .criteria { font-size:13px; color:#6b7280; margin-top:8px; }
    .criteria .ok { color: #1f8a3d; font-weight:600; }
    .criteria .bad { color: #b02a37; font-weight:600; }
    .invalid-feedback{display:block}
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="text-center mb-3">
      <div class="brand h4">InventoryApp</div>
      <div class="muted">Create a new account</div>
    </div>

    <?php
      // Normalize server-side error inputs so the template is defensive.
      $serverErrors = [];
      $serverErrorMessage = '';
      if (isset($errors) && is_array($errors)) {
        $serverErrors = $errors;
      }
      if (isset($error) && !empty($error) && !is_array($error)) {
        $serverErrorMessage = $error;
      }
      // CodeIgniter validation_errors() fallback
      if (function_exists('validation_errors') && strlen(trim(validation_errors()))) {
        // validation_errors() outputs HTML; show it at top
        echo '<div class="alert alert-danger">' . validation_errors() . '</div>';
      } elseif (!empty($serverErrors) && array_values($serverErrors) === $serverErrors) {
        // numeric array of messages
        echo '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ($serverErrors as $e) { echo '<li>' . html_escape($e) . '</li>'; }
        echo '</ul></div>';
      } elseif ($serverErrorMessage) {
        echo '<div class="alert alert-danger">' . html_escape($serverErrorMessage) . '</div>';
      }
    ?>

    <form id="registerForm" method="post" action="<?= site_url('auth/register'); ?>" novalidate>
      <!-- Email -->
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" required class="form-control <?= (function_exists('form_error') && form_error('email')) ? 'is-invalid' : '' ?>"
               placeholder="your@email.com"
               value="<?= (function_exists('set_value') ? set_value('email') : (isset($_POST['email']) ? html_escape($_POST['email']) : '')); ?>">
        <?php
          if (function_exists('form_error') && form_error('email')) {
            echo '<div class="invalid-feedback">' . form_error('email') . '</div>';
          } elseif (isset($serverErrors['email'])) {
            echo '<div class="invalid-feedback">' . html_escape($serverErrors['email']) . '</div>';
          }
        ?>
      </div>

      <!-- Username -->
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input id="username" name="username" required class="form-control <?= (function_exists('form_error') && form_error('username')) ? 'is-invalid' : '' ?>"
               placeholder="choose username" value="<?= (function_exists('set_value') ? set_value('username') : (isset($_POST['username']) ? html_escape($_POST['username']) : '')); ?>">
        <?php
          if (function_exists('form_error') && form_error('username')) {
            echo '<div class="invalid-feedback">' . form_error('username') . '</div>';
          } elseif (isset($serverErrors['username'])) {
            echo '<div class="invalid-feedback">' . html_escape($serverErrors['username']) . '</div>';
          }
        ?>
      </div>

      <!-- Display name -->
      <div class="mb-3">
        <label class="form-label" for="display_name">Display name</label>
        <input id="display_name" name="display_name" class="form-control <?= (function_exists('form_error') && form_error('display_name')) ? 'is-invalid' : '' ?>"
               placeholder="Full name (optional)" value="<?= (function_exists('set_value') ? set_value('display_name') : (isset($_POST['display_name']) ? html_escape($_POST['display_name']) : '')); ?>">
        <?php
          if (function_exists('form_error') && form_error('display_name')) {
            echo '<div class="invalid-feedback">' . form_error('display_name') . '</div>';
          } elseif (isset($serverErrors['display_name'])) {
            echo '<div class="invalid-feedback">' . html_escape($serverErrors['display_name']) . '</div>';
          }
        ?>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input id="password" type="password" name="password" required class="form-control <?= (function_exists('form_error') && form_error('password')) ? 'is-invalid' : '' ?>"
               placeholder="min 8 characters">
        <?php
          if (function_exists('form_error') && form_error('password')) {
            echo '<div class="invalid-feedback">' . form_error('password') . '</div>';
          } elseif (isset($serverErrors['password'])) {
            echo '<div class="invalid-feedback">' . html_escape($serverErrors['password']) . '</div>';
          }
        ?>

        <!-- Strength meter UI -->
        <div class="mt-2">
          <div class="strength-bar" aria-hidden="true"><div id="strengthFill" class="strength-fill"></div></div>
          <div id="strengthText" style="margin-top:6px;font-size:13px;color:#475569">Strength: <strong id="strengthLabel">—</strong></div>

          <div class="criteria" id="pwCriteria">
            <div id="crit_len" class="bad">• At least 8 characters</div>
            <div id="crit_upper" class="bad">• One uppercase letter (A–Z)</div>
            <div id="crit_digit" class="bad">• One digit (0–9)</div>
            <div id="crit_special" class="bad">• One special character (e.g. !@#$%)</div>
          </div>
        </div>
      </div>

      <!-- Confirm password -->
      <div class="mb-3">
        <label class="form-label" for="password_confirm">Confirm password</label>
        <input id="password_confirm" type="password" name="password_confirm" required class="form-control <?= (function_exists('form_error') && form_error('password_confirm')) ? 'is-invalid' : '' ?>"
               placeholder="repeat password">
        <?php
          if (function_exists('form_error') && form_error('password_confirm')) {
            echo '<div class="invalid-feedback">' . form_error('password_confirm') . '</div>';
          } elseif (isset($serverErrors['password_confirm'])) {
            echo '<div class="invalid-feedback">' . html_escape($serverErrors['password_confirm']) . '</div>';
          }
        ?>
        <div id="confirmFeedback" class="invalid-feedback" style="display:none"></div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="agree" name="agree" required>
          <label class="form-check-label" for="agree">I agree to the terms</label>
        </div>
        <div><a href="<?= site_url('auth/login'); ?>">Already have an account?</a></div>
      </div>

      <div class="d-grid">
        <button id="submitBtn" class="btn btn-primary" type="submit">Create account</button>
      </div>
    </form>

    <div class="footer-note">
      We recommend using a strong, unique password.
    </div>
  </div>

  <script>
    // Small helper functions
    function $(id){ return document.getElementById(id); }
    function hasUpper(s){ return /[A-Z]/.test(s); }
    function hasDigit(s){ return /[0-9]/.test(s); }
    function hasSpecial(s){ return /[!@#\$%\^&\*\(\)\-_=\+\[\]\{\}\|;:'",.<>\/\?`~]/.test(s); }

    const password = $('password');
    const passwordConfirm = $('password_confirm');
    const strengthFill = $('strengthFill');
    const strengthLabel = $('strengthLabel');
    const crit_len = $('crit_len');
    const crit_upper = $('crit_upper');
    const crit_digit = $('crit_digit');
    const crit_special = $('crit_special');
    const confirmFeedback = $('confirmFeedback');
    const submitBtn = $('submitBtn');
    const form = $('registerForm');

    function evaluateStrength(pw){
      if (!pw) return { score:0, label:'Very weak', color:'#ef4444' };
      let score = 0;
      if (pw.length >= 8) score++;
      if (hasUpper(pw)) score++;
      if (hasDigit(pw)) score++;
      if (hasSpecial(pw)) score++;
      const labels = ['Very weak','Weak','Okay','Good','Strong'];
      const colors = ['#ef4444','#f97316','#f59e0b','#10b981','#0b61d8'];
      return { score, label: labels[score], color: colors[score] };
    }

    function updateStrengthUI(){
      const pw = password.value || '';
      const res = evaluateStrength(pw);
      const pct = (res.score / 4) * 100;
      strengthFill.style.width = pct + '%';
      strengthFill.style.background = res.color;
      $('strengthLabel').textContent = res.label;
      // criteria
      if (pw.length >= 8) { crit_len.classList.remove('bad'); crit_len.classList.add('ok'); } else { crit_len.classList.remove('ok'); crit_len.classList.add('bad'); }
      if (hasUpper(pw)) { crit_upper.classList.remove('bad'); crit_upper.classList.add('ok'); } else { crit_upper.classList.remove('ok'); crit_upper.classList.add('bad'); }
      if (hasDigit(pw)) { crit_digit.classList.remove('bad'); crit_digit.classList.add('ok'); } else { crit_digit.classList.remove('ok'); crit_digit.classList.add('bad'); }
      if (hasSpecial(pw)) { crit_special.classList.remove('bad'); crit_special.classList.add('ok'); } else { crit_special.classList.remove('ok'); crit_special.classList.add('bad'); }
    }

    function checkConfirm(){
      const a = password.value || '';
      const b = passwordConfirm.value || '';
      if (!b) { confirmFeedback.style.display = 'none'; passwordConfirm.classList.remove('is-invalid'); passwordConfirm.classList.remove('is-valid'); return true; }
      if (a === b) {
        confirmFeedback.style.display = 'none';
        passwordConfirm.classList.remove('is-invalid');
        passwordConfirm.classList.add('is-valid');
        return true;
      } else {
        confirmFeedback.style.display = 'block';
        confirmFeedback.textContent = 'Passwords do not match';
        passwordConfirm.classList.remove('is-valid');
        passwordConfirm.classList.add('is-invalid');
        return false;
      }
    }

    password.addEventListener('input', function(){ updateStrengthUI(); checkConfirm(); });
    passwordConfirm.addEventListener('input', checkConfirm);

    // Client-side validation on submit
    form.addEventListener('submit', function(e){
      // clear previous server-side inline classes for fresh checks
      const fields = ['email','username','display_name','password','password_confirm','agree'];
      fields.forEach(f => {
        const el = document.getElementsByName(f)[0];
        if (el) { el.classList.remove('is-invalid'); }
      });

      let ok = true;
      // email
      const emailEl = $('email');
      if (!emailEl.value || !/^\S+@\S+\.\S+$/.test(emailEl.value)) {
        ok = false;
        emailEl.classList.add('is-invalid');
        if (!emailEl.nextElementSibling || !emailEl.nextElementSibling.classList.contains('invalid-feedback')) {
          const msg = document.createElement('div'); msg.className='invalid-feedback'; msg.textContent='Please enter a valid email'; emailEl.parentNode.appendChild(msg);
        }
      }

      // username
      const usernameEl = $('username');
      if (!usernameEl.value || usernameEl.value.length < 3) {
        ok = false;
        usernameEl.classList.add('is-invalid');
        if (!usernameEl.nextElementSibling || !usernameEl.nextElementSibling.classList.contains('invalid-feedback')) {
          const msg = document.createElement('div'); msg.className='invalid-feedback'; msg.textContent='Username must be at least 3 characters'; usernameEl.parentNode.appendChild(msg);
        }
      }

      // password strength (require at least score 2)
      const pwRes = evaluateStrength(password.value || '');
      if (pwRes.score < 2) {
        ok = false;
        password.classList.add('is-invalid');
        if (!password.nextElementSibling || !password.nextElementSibling.classList.contains('invalid-feedback')) {
          const msg = document.createElement('div'); msg.className='invalid-feedback'; msg.textContent='Password is too weak'; password.parentNode.appendChild(msg);
        }
      }

      // confirm
      if (!checkConfirm()) { ok = false; }

      // agree checkbox
      const agree = document.getElementsByName('agree')[0];
      if (!agree || !agree.checked) {
        ok = false;
        const parent = agree ? agree.closest('.form-check') : null;
        if (parent) {
          let msg = parent.querySelector('.invalid-feedback');
          if (!msg) { msg = document.createElement('div'); msg.className='invalid-feedback'; parent.appendChild(msg); }
          msg.style.display = 'block';
          msg.textContent = 'You must agree to the terms';
        }
      }

      if (!ok) {
        e.preventDefault();
        // scroll to first invalid
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior:'smooth', block:'center' });
        return false;
      }

      // otherwise let the form submit (server will validate again)
    });

    // initialize UI if any existing value (e.g. after server validation failure)
    updateStrengthUI();
    checkConfirm();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
