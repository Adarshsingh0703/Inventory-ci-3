<?php // view/category/form.php
// A refreshed, site-styled category form view (Create / Edit)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= isset($category) ? 'Edit Category' : 'Create Category' ?></title>
  <style>
    :root{ --bg:#f6f8fb; --card:#ffffff; --muted:#6b7280; --accent:#2563eb; --accent-2:#0b61d8; --danger:#ef4444; --shadow: 0 8px 28px rgba(20,20,50,0.06); font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    html,body{height:100%;margin:0;background:var(--bg);color:#111827}
    .container{max-width:820px;margin:28px auto;padding:18px}
    .card{background:var(--card);border-radius:12px;padding:20px;box-shadow:var(--shadow)}
    h2{margin:0 0 8px 0}
    .muted{color:var(--muted)}
    label{display:block;margin:8px 0 6px;font-weight:600}
    input[type="text"], select{padding:10px;border:1px solid #e6e9ef;border-radius:8px;width:100%;box-sizing:border-box}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width:720px){ .row{grid-template-columns:1fr} }
    .actions{display:flex;gap:10px;justify-content:flex-end;margin-top:14px}
    button{background:var(--accent);color:#fff;border:0;padding:10px 14px;border-radius:8px;cursor:pointer}
    button.ghost{background:transparent;color:var(--accent);border:1px solid rgba(37,99,235,0.12)}
    .msg{margin:10px 0;padding:10px;border-radius:8px}
    .err{background:#fff1f2;color:#9f1239;border:1px solid rgba(239,68,68,0.08)}
    .small{font-size:13px;color:var(--muted)}
    a.cancel{color:var(--muted);text-decoration:none;padding:10px 12px;border-radius:8px}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2><?= isset($category) ? 'Edit Category' : 'Create Category' ?></h2>
      <div class="small muted" style="margin-bottom:12px">Use this form to create or update a product category. Parent can be left empty for top-level categories.</div>

      <?php if (validation_errors()): ?>
        <div class="msg err"><?= validation_errors(); ?></div>
      <?php endif; ?>

      <form method="post" action="<?= isset($category) ? site_url('categories/edit/'.$category['id']) : site_url('categories/create'); ?>">
        <!-- keep CSRF hidden input if your framework provides one -->

        <div class="row">
          <div>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?= set_value('name', isset($category['name']) ? $category['name'] : ''); ?>" required />
          </div>

          <div>
            <label for="parent_id">Parent Category (optional)</label>
            <select id="parent_id" name="parent_id">
              <option value="">-- None --</option>
              <?php foreach (($parents ?? []) as $p): ?>
                <option value="<?= (int)$p['id']; ?>"
                  <?= set_select('parent_id', (string)$p['id'], isset($category['parent_id']) && (string)$category['parent_id'] === (string)$p['id']); ?>>
                  <?= html_escape($p['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- optional description field for improved UX -->
        <div style="margin-top:12px">
          <label for="description">Description (optional)</label>
          <input id="description" name="description" type="text" value="<?= set_value('description', isset($category['description']) ? $category['description'] : ''); ?>" />
          <div class="small muted">Short note to describe this category (used only for admin reference).</div>
        </div>

        <div class="actions">
          <a class="cancel" href="<?= site_url('categories'); ?>">Cancel</a>
          <button type="submit"><?= isset($category) ? 'Update' : 'Create' ?></button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
