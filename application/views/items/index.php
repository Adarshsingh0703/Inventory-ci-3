<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Inventory — Items</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --bg:#f6f8fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#2563eb;
      --accent-2:#0b61d8;
      --danger:#ef4444;
      --success:#10b981;
      --shadow:0 8px 28px rgba(20,20,50,0.06);
      font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    html,body{height:100%;margin:0;background:var(--bg);color:#111827}
    .app{display:flex;min-height:100vh}

    /* SIDEBAR */
    .sidebar{
      width:260px;
      background:linear-gradient(180deg,#0f172a,#08122a);
      color:#fff;
      padding:22px 18px;
      box-shadow:rgba(2,6,23,0.2) 0 3px 12px;
    }
    .brand{
      font-weight:700;
      font-size:18px;
      margin-bottom:12px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .brand .logo{
      width:36px;
      height:36px;
      border-radius:8px;
      background:linear-gradient(90deg,#2563eb,#7c3aed);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:700;
    }
    .muted{color:var(--muted)}
    .mini{font-size:13px;color:var(--muted)}
    .nav{margin-top:18px}
    .nav a{
      display:block;
      padding:10px 12px;
      border-radius:8px;
      color:rgba(255,255,255,0.9);
      text-decoration:none;
      margin-bottom:6px;
    }
    .nav a.active{background:rgba(255,255,255,0.06)}

    /* MAIN */
    .main{flex:1;padding:22px;max-width:1400px;margin:0 auto}
    .topbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:18px;
      gap:12px;
      flex-wrap:wrap;
    }
    .toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
    .search{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

    .card{
      background:var(--card);
      border-radius:12px;
      padding:16px;
      box-shadow:var(--shadow);
      margin-bottom:16px;
    }

    /* inputs / buttons */
    input[type="text"],input[type="number"],textarea,select{
      padding:10px;
      border:1px solid #e6e9ef;
      border-radius:8px;
      width:100%;
      box-sizing:border-box;
      font-size:14px;
    }
    button{
      background:var(--accent);
      color:#fff;
      border:0;
      padding:10px 12px;
      border-radius:8px;
      cursor:pointer;
      font-size:14px;
    }
    button.ghost{
      background:transparent;
      color:var(--accent);
      border:1px solid rgba(37,99,235,0.12);
    }
    button.warn{background:var(--danger)}

    .flex{display:flex;gap:8px;align-items:center}
    .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
    @media (max-width:820px){
      .sidebar{display:none}
      .grid-2{grid-template-columns:1fr}
      .main{padding:16px}
    }

    table{
      border-collapse:collapse;
      width:100%;
      margin-top:12px;
      border-radius:8px;
      overflow:hidden;
      font-size:14px;
    }
    thead{background:#f3f6fb}
    th,td{
      padding:12px 14px;
      text-align:left;
      border-bottom:1px solid #f1f3f5;
      vertical-align:top;
    }
    tbody tr:hover{background:#fbfdff}

    .pager{
      display:flex;
      gap:8px;
      align-items:center;
      justify-content:flex-end;
      margin-top:14px;
    }
    .pagebtn{
      padding:6px 12px;
      border:1px solid #e6e9ef;
      background:#ffffff;
      cursor:pointer;
      border-radius:6px;
      font-size:13px;
      color:#111827;      /* readable text */
      min-width:44px;
    }
    .pagebtn.active{
      background:var(--accent-2);
      color:#ffffff;
      border-color:var(--accent-2);
    }
    .pagebtn:disabled{
      opacity:0.55;
      cursor:default;
    }

    /* messages */
    .msg{margin:10px 0;padding:10px;border-radius:8px;font-size:14px}
    .err{background:#fff1f2;color:#9f1239;border:1px solid rgba(239,68,68,0.08)}
    .ok{background:#ecfdf5;color:#065f46;border:1px solid rgba(16,185,129,0.08)}

    /* modal */
    .modal-backdrop{
      position:fixed;
      inset:0;
      background:rgba(2,6,23,0.45);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:60;
    }
    .modal{
      background:var(--card);
      width:720px;
      max-width:95%;
      border-radius:12px;
      padding:18px;
      box-shadow:var(--shadow);
    }
    .label{font-weight:600;margin-bottom:6px;display:block}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}

    /* bulk csv */
    .bulk-subtitle{font-size:13px;color:var(--muted);margin-bottom:10px}
    .bulk-note{font-size:12px;color:#6b7280;margin-top:4px}
    .bulk-link{
      display:inline-block;
      padding:8px 12px;
      border-radius:8px;
      background:#eef2ff;
      color:#3730a3;
      text-decoration:none;
      font-size:14px;
      font-weight:500;
    }
    .bulk-link:hover{background:#e0e7ff}
    .small{font-size:13px}
  </style>
</head>
<body>
<div class="app">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <span class="logo">IA</span>
      Inventory App
    </div>
    <div class="mini muted">Manage items, categories & stock</div>
    <nav class="nav" aria-label="Main navigation">
      <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
      <a href="<?= site_url('items'); ?>" class="active">Items</a>
      <a href="<?= site_url('categories'); ?>">Categories</a>
      <a href="<?= site_url('audit'); ?>">Audit Logs</a>
      <a href="<?= site_url('auth/logout'); ?>">Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main">
    <div class="topbar">
      <div>
        <h2 style="margin:0 0 6px 0">Items</h2>
        <div class="mini muted">Create, import, and manage inventory items</div>
      </div>

      <div class="toolbar">
        <div class="search">
          <input id="qInput" type="text" placeholder="Search by name or SKU" oninput="debounced()" style="min-width:220px">
          <select id="perPageSelect" onchange="goToPage(1)">
            <option value="5">5 / page</option>
            <option value="10" selected>10 / page</option>
            <option value="20">20 / page</option>
            <option value="50">50 / page</option>
          </select>
          <button class="ghost" type="button" onclick="clearSearch()">Clear</button>
        </div>
        <button type="button" onclick="openCreateModal()">+ Add Item</button>
      </div>
    </div>

    <!-- Flash messages from CSV import/export -->
    <?php if (!empty($flash_success)): ?>
      <div class="msg ok"><?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="msg err"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Bulk CSV tools -->
    <section class="card">
      <h3 style="margin:0 0 4px 0;font-size:16px;">Bulk CSV (Items)</h3>
      <div class="bulk-subtitle">
        Download all items as CSV, edit them in Excel/Sheets, then upload to create or update in bulk.
      </div>
      <div class="grid-2">
        <div>
          <div class="small muted" style="margin-bottom:6px;">Download current data</div>
          <a class="bulk-link" href="<?= site_url('items/export-csv'); ?>">Download all items (CSV)</a>
          <p class="bulk-note">
            Use this file as a template. You can adjust prices, quantities, or descriptions and then re-upload.
          </p>
        </div>
        <div>
          <div class="small muted" style="margin-bottom:6px;">Upload CSV (create / update)</div>
          <form action="<?= site_url('items/import-csv'); ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <div style="margin-top:8px">
              <button type="submit">Upload CSV</button>
            </div>
          </form>
          <p class="bulk-note">
            Expected header columns:
            <strong>name, sku, description, quantity, price, category_id</strong>.<br>
            If <strong>sku</strong> matches an existing item, it will be updated; otherwise a new item is created.
          </p>
        </div>
      </div>
    </section>

    <!-- Items table card -->
    <section class="card">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
        <div class="mini muted">Showing <strong id="countDisplay">0</strong> items</div>
        <div class="mini muted" style="margin-left:auto">Page <span id="pageDisplay">1</span></div>
      </div>

      <div id="listMsg"></div>

      <table aria-live="polite">
        <thead>
        <tr>
          <th style="width:60px">#</th>
          <th style="width:80px">ID</th>
          <th>Name</th>
          <th style="width:120px">SKU</th>
          <th style="width:90px">Qty</th>
          <th style="width:110px">Price</th>
          <th style="width:120px">Category ID</th>
          <th style="width:170px">Actions</th>
        </tr>
        </thead>
        <tbody id="itemsBody">
        <tr><td colspan="8">Loading…</td></tr>
        </tbody>
      </table>

      <div class="pager" id="pager"></div>
    </section>
  </main>
</div>

<!-- Create / Edit Modal -->
<div id="modalBackdrop" class="modal-backdrop">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <h3 id="modalTitle">Add Item</h3>
    <div id="createMsg" style="margin-top:8px"></div>

    <form id="itemForm" onsubmit="return onCreateOrUpdate(event)">
      <input type="hidden" id="item_id">
      <div class="grid-2" style="margin-top:12px">
        <div>
          <label class="label" for="c_name">Name</label>
          <input id="c_name" type="text" required>
        </div>
        <div>
          <label class="label" for="c_sku">SKU (optional)</label>
          <input id="c_sku" type="text">
        </div>
        <div>
          <label class="label" for="c_quantity">Quantity</label>
          <input id="c_quantity" type="number" min="0" step="1" value="0" required>
        </div>
        <div>
          <label class="label" for="c_price">Price</label>
          <input id="c_price" type="number" min="0" step="0.01" value="0.00" required>
        </div>
        <div style="grid-column:1 / -1">
          <label class="label" for="c_description">Description</label>
          <textarea id="c_description" rows="3"></textarea>
        </div>
        <div>
          <label class="label" for="c_category_id">Category (ID, optional)</label>
          <input id="c_category_id" type="number" min="1" step="1" placeholder="e.g. 3">
        </div>
      </div>

      <div class="actions">
        <button type="button" class="ghost" onclick="closeModal()">Cancel</button>
        <button id="saveBtn" type="submit">Save Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Axios + JS -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
  const api = axios.create({
    baseURL: '<?= site_url('api/items'); ?>',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });

  const state = { page:1, per_page:10, total:0, total_pages:0, offset:0, q:'' };
  let debounceId = null;

  function setMsg(el, html, ok=false){
    el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : '';
  }
  function esc(s){
    return (s==null?'':String(s))
      .replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  }

  async function loadItems(){
    const listMsg = document.getElementById('listMsg');
    setMsg(listMsg,'');
    try{
      const params = new URLSearchParams();
      if(state.q) params.set('q', state.q);
      params.set('page', state.page);
      params.set('per_page', state.per_page);

      const { data } = await api.get('?'+params.toString());
      const items = data.items || [];
      const pag = data.pagination || { total:0, page:state.page, per_page:state.per_page, total_pages:0, offset:0 };

      state.total       = pag.total;
      state.total_pages = pag.total_pages;
      state.per_page    = pag.per_page;
      state.page        = pag.page;
      state.offset      = pag.offset || ((state.page-1)*state.per_page);

      const tbody = document.getElementById('itemsBody');
      if(!items.length){
        tbody.innerHTML = '<tr><td colspan="8" class="mini muted">No items found.</td></tr>';
        document.getElementById('countDisplay').innerText = 0;
        document.getElementById('pageDisplay').innerText  = '1 / 1';
        renderPager();
        return;
      }

      const startSerial = state.offset + 1;
      tbody.innerHTML = items.map((it, idx) => `
        <tr id="row-${it.id}">
          <td>${startSerial + idx}</td>
          <td>${it.id}</td>
          <td><strong>${esc(it.name)}</strong></td>
          <td class="mini">${esc(it.sku || '')}</td>
          <td>${it.quantity}</td>
          <td>${Number(it.price).toFixed(2)}</td>
          <td class="mini">${it.category_id ?? ''}</td>
          <td>
            <button type="button" class="ghost" onclick="openEdit(${it.id})">Edit</button>
            <button type="button" class="warn" onclick="onDelete(${it.id})">Delete</button>
          </td>
        </tr>
      `).join('');

      document.getElementById('countDisplay').innerText = state.total;
      document.getElementById('pageDisplay').innerText  = state.page + ' / ' + (state.total_pages || 1);
      renderPager();
    }catch(err){
      const msg = err?.response?.data?.error || err?.message || 'Failed to load';
      setMsg(listMsg, msg, false);
    }
  }

  // NEW pager: First, previous page number, current, next page number, Last
  function renderPager(){
    const pager = document.getElementById('pager');
    pager.innerHTML = '';
    const total = state.total_pages || 0;
    const current = state.page || 1;

    if (total <= 1) return;

    const makeBtn = (label, targetPage, disabled, extraClass='') => {
      const b = document.createElement('button');
      b.className = 'pagebtn ' + extraClass;
      b.textContent = label;
      b.disabled = !!disabled;
      if (!disabled) {
        b.onclick = () => goToPage(targetPage);
      }
      return b;
    };

    // First
    pager.appendChild(makeBtn('First', 1, current === 1));

    // Previous page number
    if (current > 1) {
      pager.appendChild(makeBtn(String(current - 1), current - 1, false));
    }

    // Current page (highlighted, disabled)
    pager.appendChild(makeBtn(String(current), current, true, 'active'));

    // Next page number
    if (current < total) {
      pager.appendChild(makeBtn(String(current + 1), current + 1, false));
    }

    // Last
    pager.appendChild(makeBtn('Last', total, current === total));
  }

  function goToPage(p){
    if(p < 1) p = 1;
    if(state.total_pages && p > state.total_pages) p = state.total_pages;
    state.page = p;
    state.q = document.getElementById('qInput').value.trim();
    state.per_page = parseInt(document.getElementById('perPageSelect').value, 10) || 10;
    loadItems();
  }

  function debounced(){
    clearTimeout(debounceId);
    debounceId = setTimeout(()=>{ goToPage(1); }, 350);
  }
  function clearSearch(){
    document.getElementById('qInput').value = '';
    goToPage(1);
  }

  /* Modal helpers */
  function showModal(show=true){
    const b = document.getElementById('modalBackdrop');
    b.style.display = show ? 'flex' : 'none';
    if(show) document.getElementById('c_name').focus();
  }
  function openCreateModal(){
    document.getElementById('modalTitle').innerText = 'Add Item';
    document.getElementById('itemForm').reset();
    document.getElementById('item_id').value = '';
    setMsg(document.getElementById('createMsg'),'');
    showModal(true);
  }
  function openEditModalData(item){
    document.getElementById('modalTitle').innerText = 'Edit Item';
    document.getElementById('item_id').value = item.id;
    document.getElementById('c_name').value = item.name;
    document.getElementById('c_sku').value = item.sku || '';
    document.getElementById('c_quantity').value = item.quantity;
    document.getElementById('c_price').value = item.price;
    document.getElementById('c_description').value = item.description || '';
    document.getElementById('c_category_id').value = item.category_id || '';
    setMsg(document.getElementById('createMsg'),'');
    showModal(true);
  }
  function closeModal(){ showModal(false); }

  /* Create / Update */
  async function onCreateOrUpdate(e){
    e.preventDefault();
    const id = document.getElementById('item_id').value;
    const payload = {
      name: document.getElementById('c_name').value.trim(),
      sku: document.getElementById('c_sku').value.trim(),
      description: document.getElementById('c_description').value.trim(),
      quantity: Number(document.getElementById('c_quantity').value),
      price: Number(document.getElementById('c_price').value),
      category_id: document.getElementById('c_category_id').value
        ? Number(document.getElementById('c_category_id').value)
        : ''
    };

    const msgEl = document.getElementById('createMsg');
    try{
      if(id){
        await api.post('/'+id, Object.assign({_method:'PUT'}, payload));
        setMsg(msgEl,'Updated', true);
      }else{
        const { data } = await api.post('', payload);
        setMsg(msgEl,'Created (ID: '+(data.id || '')+')', true);
      }
      closeModal();
      loadItems();
    }catch(err){
      const res = err?.response;
      if(res && res.status === 422 && res.data?.errors){
        const first = Object.values(res.data.errors)[0];
        setMsg(msgEl, first || 'Validation error', false);
      }else{
        setMsg(msgEl, res?.data?.error || err?.message || 'Save failed', false);
      }
    }
    return false;
  }

  async function openEdit(id){
    try{
      const { data } = await api.get('/'+id);
      openEditModalData(data);
    }catch(err){
      alert(err?.response?.data?.error || err?.message || 'Failed to fetch item');
    }
  }

  async function onDelete(id){
    if(!confirm('Delete item '+id+'?')) return;
    const listMsg = document.getElementById('listMsg');
    setMsg(listMsg,'');
    try{
      await api.post('/'+id, {_method:'DELETE'});
      setMsg(listMsg,'Deleted', true);
      loadItems();
    }catch(err){
      const msg = err?.response?.data?.error || err?.message || 'Delete failed';
      setMsg(listMsg, msg, false);
    }
  }

  // init
  (function init(){
    document.getElementById('perPageSelect').value = state.per_page;
    document.getElementById('modalBackdrop').addEventListener('click', ev=>{
      if(ev.target === ev.currentTarget) closeModal();
    });
    goToPage(1);
  })();
</script>
</body>
</html>
