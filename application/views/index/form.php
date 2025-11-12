<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= isset($item) ? 'Edit Item' : 'Create Item' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:760px;margin:24px auto;padding:0 16px}
    label{display:block;margin:8px 0 4px}
    input,select,textarea{width:100%;padding:8px}
    textarea{min-height:90px}
    .err{background:#ffe6e6;color:#a40000;margin:10px 0;padding:8px;border-radius:6px}
    a{color:#0b61d8;text-decoration:none}
    .actions{margin-top:12px}
    button{padding:8px 12px;cursor:pointer}
  </style>
</head>
<body>
  <h2><?= isset($item) ? 'Edit Item' : 'Create Item' ?></h2>

  <?php if (validation_errors()): ?>
    <div class="err"><?= validation_errors(); ?></div>
  <?php endif; ?>

  <form method="post" action="<?= isset($item) ? site_url('items/edit/'.$item['id']) : site_url('items/create'); ?>">
    <label>Name</label>
    <input type="text" name="name" value="<?= set_value('name', isset($item['name']) ? $item['name'] : ''); ?>" required>

    <label>SKU (optional, unique)</label>
    <input type="text" name="sku" value="<?= set_value('sku', isset($item['sku']) ? $item['sku'] : ''); ?>">

    <label>Description</label>
    <textarea name="description"><?= set_value('description', isset($item['description']) ? $item['description'] : ''); ?></textarea>

    <label>Quantity</label>
    <input type="number" name="quantity" min="0" step="1" value="<?= set_value('quantity', isset($item['quantity']) ? (int)$item['quantity'] : 0); ?>" required>

    <label>Price</label>
    <input type="number" name="price" min="0" step="0.01" value="<?= set_value('price', isset($item['price']) ? (float)$item['price'] : 0.00); ?>" required>

    <label>Category</label>
    <select name="category_id">
      <option value="">-- None --</option>
      <?php foreach (($categories ?? []) as $c): ?>
        <option value="<?= (int)$c['id']; ?>"
          <?= set_select('category_id',
                         (string)$c['id'],
                         isset($item['category_id']) && (string)$item['category_id'] === (string)$c['id']); ?>>
          <?= html_escape($c['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <div class="actions">
      <button type="submit"><?= isset($item) ? 'Update' : 'Create' ?></button>
      <a href="<?= site_url('items'); ?>">Cancel</a>
    </div>
  </form>
</body>
</html>
