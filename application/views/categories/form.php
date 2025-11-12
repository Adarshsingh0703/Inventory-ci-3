<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= isset($category) ? 'Edit Category' : 'Create Category' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:700px;margin:24px auto;padding:0 16px}
    label{display:block;margin:8px 0 4px}
    input,select{width:100%;padding:8px}
    .err{background:#ffe6e6;color:#a40000;margin:10px 0;padding:8px;border-radius:6px}
    a{color:#0b61d8;text-decoration:none}
    .actions{margin-top:12px}
    button{padding:8px 12px;cursor:pointer}
  </style>
</head>
<body>
  <h2><?= isset($category) ? 'Edit Category' : 'Create Category' ?></h2>

  <?php if (validation_errors()): ?>
    <div class="err"><?= validation_errors(); ?></div>
  <?php endif; ?>

  <form method="post" action="<?= isset($category) ? site_url('categories/edit/'.$category['id']) : site_url('categories/create'); ?>">
    <label>Name</label>
    <input type="text" name="name" value="<?= set_value('name', isset($category['name']) ? $category['name'] : ''); ?>" required>

    <label style="margin-top:8px">Parent (optional)</label>
    <select name="parent_id">
      <option value="">-- None --</option>
      <?php foreach (($parents ?? []) as $p): ?>
        <option value="<?= (int)$p['id']; ?>"
          <?= set_select('parent_id',
                         (string)$p['id'],
                         isset($category['parent_id']) && (string)$category['parent_id'] === (string)$p['id']); ?>>
          <?= html_escape($p['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <div class="actions">
      <button type="submit"><?= isset($category) ? 'Update' : 'Create' ?></button>
      <a href="<?= site_url('categories'); ?>">Cancel</a>
    </div>
  </form>
</body>
</html>
