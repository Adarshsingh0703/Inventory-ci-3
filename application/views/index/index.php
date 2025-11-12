<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Items</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:1100px;margin:24px auto;padding:0 16px}
    a{color:#0b61d8;text-decoration:none}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    .topbar a{margin-right:12px}
    form.search{margin:12px 0}
    input[type="text"]{padding:8px;width:260px;max-width:100%}
    button{padding:8px 12px;cursor:pointer}
  </style>
</head>
<body>
  <h2>Items</h2>
  <div class="topbar">
    <a href="<?= site_url('items/create'); ?>">+ New Item</a>
    <a href="<?= site_url('categories'); ?>">Categories</a>
    <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
    <a href="<?= site_url('auth/logout'); ?>">Logout</a>
  </div>

  <form method="get" action="<?= site_url('items'); ?>" class="search">
    <input type="text" name="q" placeholder="Search by name or SKU" value="<?= html_escape($q ?? ''); ?>">
    <button type="submit">Search</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>SKU</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Category</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
      <tr><td colspan="7">No items found.</td></tr>
    <?php else: foreach ($items as $it): ?>
      <tr>
        <td><?= (int)$it['id']; ?></td>
        <td><?= html_escape($it['name']); ?></td>
        <td><?= html_escape($it['sku']); ?></td>
        <td><?= (int)$it['quantity']; ?></td>
        <td><?= number_format((float)$it['price'], 2); ?></td>
        <td>
          <?php
            $cid = isset($it['category_id']) ? (int)$it['category_id'] : 0;
            echo isset($category_map[$cid]) ? html_escape($category_map[$cid]) : '-';
          ?>
        </td>
        <td>
          <a href="<?= site_url('items/edit/'.$it['id']); ?>">Edit</a> |
          <a href="<?= site_url('items/delete/'.$it['id']); ?>" onclick="return confirm('Delete this item?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</body>
</html>
