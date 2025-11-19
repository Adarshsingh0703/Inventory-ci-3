<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Inventory — Audit Logs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg:#f6f8fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#2563eb;
      --accent-2:#0b61d8;
      --danger:#ef4444;
      --shadow:0 8px 28px rgba(20,20,50,0.06);
      font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    html,body{height:100%;margin:0;background:var(--bg);color:#111827}

    .app{display:flex;min-height:100vh}

    /* Sidebar */
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

    /* Main */
    .main{flex:1;padding:22px;max-width:1400px;margin:0 auto}
    .topbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:18px;
      gap:12px;
      flex-wrap:wrap;
    }
    .top-actions{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
    }

    .card{
      background:var(--card);
      border-radius:12px;
      padding:16px;
      box-shadow:var(--shadow);
      margin-bottom:16px;
    }

    /* Buttons, inputs */
    button{
      background:var(--accent);
      color:#fff;
      border:0;
      padding:8px 12px;
      border-radius:8px;
      cursor:pointer;
      font-size:14px;
    }
    .btn-outline-light{
      background:transparent;
      color:var(--accent);
      border:1px solid rgba(37,99,235,0.18);
      border-radius:8px;
      padding:8px 12px;
      text-decoration:none;
      font-size:14px;
    }

    input,textarea,select{
      padding:9px 10px;
      width:100%;
      box-sizing:border-box;
      border:1px solid #e6e9ef;
      border-radius:8px;
      font-size:14px;
    }

    .filters{
      display:grid;
      grid-template-columns:repeat(4,minmax(0,1fr));
      gap:10px;
      align-items:flex-end;
    }
    .filters label{
      display:block;
      margin-bottom:4px;
      font-size:13px;
      font-weight:600;
      color:#374151;
    }

    /* Table */
    table{
      border-collapse:collapse;
      width:100%;
      margin-top:8px;
      font-size:13px;
      border-radius:10px;
      overflow:hidden;
    }
    thead{background:#f3f6fb}
    th,td{
      border-bottom:1px solid #f1f3f5;
      padding:10px 12px;
      text-align:left;
      vertical-align:top;
    }
    tbody tr:hover{background:#fbfdff}
    th{font-weight:600;color:#374151}
    .nowrap{white-space:nowrap}

    /* Messages */
    .msg{margin:10px 0;padding:10px;border-radius:8px;font-size:14px}
    .err{background:#fff1f2;color:#9f1239;border:1px solid rgba(239,68,68,0.08)}
    .ok{background:#ecfdf5;color:#065f46;border:1px solid rgba(16,185,129,0.08)}

    /* Pager */
    .pager{
      display:flex;
      gap:8px;
      align-items:center;
      justify-content:flex-end;
      margin-top:14px;
    }
    .pagebtn{
      padding:6px 10px;
      border:1px solid #e6e9ef;
      background:#fff;
      cursor:pointer;
      border-radius:6px;
      font-size:13px;
    }
    .pagebtn.active{
      background:var(--accent-2);
      color:#fff;
      border-color:var(--accent-2);
    }

    /* Details cards */
    .audit-detail-card{
      background:#fdfdfd;
      border:1px solid #e5e8ef;
      border-radius:10px;
      padding:12px;
      margin-top:4px;
      box-shadow:0 2px 6px rgba(0,0,0,0.04);
    }
    .audit-detail-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:6px;
    }
    .audit-detail-badge{
      background:#eef2ff;
      color:#3730a3;
      font-size:11px;
      font-weight:600;
      padding:3px 8px;
      border-radius:6px;
      text-transform:uppercase;
      letter-spacing:.02em;
    }
    .audit-toggle-btn{
      display:inline-flex;
      align-items:center;
      gap:4px;
      font-size:12px;
      padding:4px 10px;
      border-radius:999px;
      border:1px solid #c7d2fe;
      background:#eef2ff;
      color:#3730a3;
      font-weight:500;
      cursor:pointer;
      box-shadow:0 1px 3px rgba(15,23,42,0.08);
      transition:background .15s ease,border-color .15s ease,box-shadow .15s ease,transform .15s ease;
    }
    .audit-toggle-btn:hover{
      background:#e0e7ff;
      border-color:#6366f1;
      box-shadow:0 3px 8px rgba(15,23,42,0.15);
      transform:translateY(-1px);
    }
    .audit-toggle-btn:focus-visible{
      outline:2px solid #6366f1;
      outline-offset:2px;
    }
    .audit-toggle-btn-icon{
      font-size:11px;
      line-height:1;
    }

    .audit-json-box{
      background:#fbfbff;
      border:1px solid #e9e9f2;
      border-radius:8px;
      font-family:"JetBrains Mono","Roboto Mono",monospace;
      font-size:12px;
      color:#273043;
      padding:10px;
      line-height:1.45;
      white-space:pre;
      overflow-x:auto;
      max-height:240px;
    }
    .audit-json-collapsed{
      max-height:120px;
      overflow:hidden;
      mask-image:linear-gradient(to bottom, black 60%, transparent);
    }
    .cols{display:flex;gap:10px;align-items:stretch}
    .col{flex:1;min-width:0}
    .small{font-size:13px;color:#4b5563}

    @media (max-width:1024px){
      .filters{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width:768px){
      .sidebar{display:none}
      .main{padding:16px}
      .filters{grid-template-columns:1fr}
    }
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
    <div class="mini muted">Audit trails & user activity</div>
    <nav class="nav" aria-label="Main navigation">
      <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
      <a href="<?= site_url('categories'); ?>">Categories</a>
      <a href="<?= site_url('items'); ?>">Items</a>
      <a href="<?= site_url('audit'); ?>" class="active">Audit Logs</a>
      <a href="<?= site_url('auth/logout'); ?>">Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main">
    <div class="topbar">
      <div>
        <h2 style="margin:0 0 4px 0">Audit Logs</h2>
        <div class="mini">
          Inspect who changed what, and when — with detailed before/after payloads.
        </div>
      </div>
      <div class="top-actions">
        <a class="btn-outline-light" href="<?= site_url('dashboard'); ?>">Back to Dashboard</a>
        <a class="btn-outline-light" href="<?= site_url('auth/logout'); ?>">Logout</a>
      </div>
    </div>

    <!-- Filters card -->
    <section class="card">
      <div class="small muted" style="margin-bottom:8px">Filter logs by action, user, or item.</div>
      <div class="filters">
        <div>
          <label>Action (contains)</label>
          <input id="f_action" type="text" placeholder="e.g. item.create">
        </div>
        <div>
          <label>User ID</label>
          <input id="f_user" type="number" min="1" step="1" placeholder="e.g. 1">
        </div>
        <div>
          <label>Item ID</label>
          <input id="f_item" type="number" min="1" step="1" placeholder="e.g. 10">
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end">
          <button type="button" onclick="applyFilters()">Apply</button>
          <button type="button" onclick="clearFilters()" style="background:#e5e7eb;color:#111827">Clear</button>
        </div>
      </div>
      <div id="listMsg"></div>
    </section>

    <!-- Logs table card -->
    <section class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <div class="small muted">Showing audit entries</div>
        <div class="small muted" id="metaInfo"></div>
      </div>

      <table>
        <thead>
        <tr>
          <th style="width:70px">ID</th>
          <th style="width:160px">Action</th>
          <th style="width:160px">User</th>
          <th style="width:140px">Item</th>
          <th>Details</th>
          <th style="width:170px">Created At</th>
        </tr>
        </thead>
        <tbody id="logsBody">
        <tr><td colspan="6">Loading…</td></tr>
        </tbody>
      </table>

      <div class="pager" id="pager"></div>
    </section>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
  const api = axios.create({
    baseURL: '<?= site_url('api/audit'); ?>',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });

  const state = {
    page: 1,
    per_page: 20,
    total: 0,
    total_pages: 0,
    offset: 0,
    filters: { action:'', user_id:'', item_id:'' }
  };

  function escapeHtml(s){
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#39;');
  }

  function prettyJsonHtml(obj){
    try{
      const txt = JSON.stringify(obj, null, 2);
      return escapeHtml(txt);
    }catch(e){
      return escapeHtml(String(obj));
    }
  }

  function renderDetails(detailsRaw){
    if (!detailsRaw) return '<span class="small">-</span>';

    let parsed = null;
    if (typeof detailsRaw === 'object'){
      parsed = detailsRaw;
    }else{
      try { parsed = JSON.parse(detailsRaw); } catch(e) {}
    }

    let badgeLabel = 'Data';
    let contentHtml = '';
    let isDiff = false;

    if (parsed && typeof parsed === 'object'){
      if (parsed.before || parsed.after){
        badgeLabel = 'Diff';
        const before = parsed.before || {};
        const after  = parsed.after  || {};
        isDiff = true;
        contentHtml = `
          <div class="cols">
            <div class="col">
              <div class="small" style="margin-bottom:3px;"><strong>Before</strong></div>
              <div class="audit-json-box audit-json-collapsed">${prettyJsonHtml(before)}</div>
            </div>
            <div class="col">
              <div class="small" style="margin-bottom:3px;"><strong>After</strong></div>
              <div class="audit-json-box audit-json-collapsed">${prettyJsonHtml(after)}</div>
            </div>
          </div>`;
      } else if (parsed.payload){
        badgeLabel = 'Payload';
        contentHtml = `<div class="audit-json-box audit-json-collapsed">${prettyJsonHtml(parsed.payload)}</div>`;
      } else if (parsed.deleted){
        badgeLabel = 'Deleted';
        contentHtml = `<div class="audit-json-box audit-json-collapsed">${prettyJsonHtml(parsed.deleted)}</div>`;
      } else {
        badgeLabel = 'JSON';
        contentHtml = `<div class="audit-json-box audit-json-collapsed">${prettyJsonHtml(parsed)}</div>`;
      }
    } else {
      const txt = escapeHtml(String(detailsRaw));
      contentHtml = `<div class="audit-json-box audit-json-collapsed">${txt}</div>`;
      badgeLabel = 'Text';
    }

    return `
      <div class="audit-detail-card">
        <div class="audit-detail-header">
          <span class="audit-detail-badge">${badgeLabel}</span>
          <button type="button" class="audit-toggle-btn" onclick="toggleJson(this)">
            <span class="audit-toggle-btn-icon">⇵</span>
            <span class="audit-toggle-btn-label">View full</span>
          </button>
        </div>
        ${contentHtml}
      </div>
`   ;
  }

  function toggleJson(btn){
    const card = btn.closest('.audit-detail-card');
    const boxes = card.querySelectorAll('.audit-json-box');
    boxes.forEach(box => {
      box.classList.toggle('audit-json-collapsed');
    });
  }

  function rowHtml(r){
    const user = r.user_username
      ? `${escapeHtml(r.user_username)} (${r.user_id})`
      : (r.user_id ? r.user_id : '');
    const item = r.item_name
      ? `${escapeHtml(r.item_name)} (${r.item_id})`
      : (r.item_id ? r.item_id : '');
    const detailsHtml = renderDetails(r.details);

    return `
      <tr>
        <td class="nowrap">${r.id}</td>
        <td><code>${escapeHtml(r.action)}</code></td>
        <td>${user}</td>
        <td>${item}</td>
        <td>${detailsHtml}</td>
        <td class="nowrap small">${escapeHtml(r.created_at || '')}</td>
      </tr>`;
  }

  async function loadLogs(){
    const listMsg = document.getElementById('listMsg');
    listMsg.innerHTML = '';

    const params = new URLSearchParams();
    if (state.filters.action) params.set('action', state.filters.action);
    if (state.filters.user_id) params.set('user_id', state.filters.user_id);
    if (state.filters.item_id) params.set('item_id', state.filters.item_id);
    params.set('page', state.page);
    params.set('per_page', state.per_page);

    try{
      const { data } = await api.get('?'+params.toString());
      const logs = data.logs || [];
      const pag = data.pagination || { total:0, page:state.page, per_page:state.per_page, total_pages:0, offset:0 };

      state.total       = pag.total;
      state.total_pages = pag.total_pages;
      state.per_page    = pag.per_page;
      state.page        = pag.page;
      state.offset      = pag.offset || ((state.page - 1) * state.per_page);

      const tbody = document.getElementById('logsBody');
      if (!logs.length){
        tbody.innerHTML = '<tr><td colspan="6">No logs found.</td></tr>';
        document.getElementById('metaInfo').textContent = '';
        renderPager();
        return;
      }

      tbody.innerHTML = logs.map(rowHtml).join('');

      const start = state.offset + 1;
      const end   = Math.min(state.offset + state.per_page, state.total || start + logs.length - 1);
      document.getElementById('metaInfo').textContent =
        `${start}-${end} of ${state.total || logs.length} logs`;

      renderPager();
    }catch(err){
      const msg = err?.response?.data?.error || err?.message || 'Failed to load';
      listMsg.innerHTML = '<div class="msg err">'+escapeHtml(msg)+'</div>';
    }
  }

  function renderPager(){
    const pager = document.getElementById('pager');
    pager.innerHTML = '';
    if (state.total_pages <= 1) return;

    const prev = document.createElement('button');
    prev.className = 'pagebtn';
    prev.textContent = 'Prev';
    prev.disabled = state.page <= 1;
    prev.onclick = () => goToPage(state.page - 1);
    pager.appendChild(prev);

    const windowSize = 5;
    let start = Math.max(1, state.page - Math.floor(windowSize/2));
    let end   = Math.min(state.total_pages, start + windowSize - 1);
    if (end - start + 1 < windowSize){
      start = Math.max(1, end - windowSize + 1);
    }

    for (let p = start; p <= end; p++){
      const btn = document.createElement('button');
      btn.className = 'pagebtn' + (p === state.page ? ' active' : '');
      btn.textContent = p;
      btn.onclick = () => goToPage(p);
      pager.appendChild(btn);
    }

    const next = document.createElement('button');
    next.className = 'pagebtn';
    next.textContent = 'Next';
    next.disabled = state.page >= state.total_pages;
    next.onclick = () => goToPage(state.page + 1);
    pager.appendChild(next);
  }

  function goToPage(p){
    if (p < 1) p = 1;
    if (state.total_pages && p > state.total_pages) p = state.total_pages;
    state.page = p;
    loadLogs();
  }

  function applyFilters(){
    state.filters.action  = document.getElementById('f_action').value.trim();
    state.filters.user_id = document.getElementById('f_user').value;
    state.filters.item_id = document.getElementById('f_item').value;
    state.page = 1;
    loadLogs();
  }

  function clearFilters(){
    document.getElementById('f_action').value = '';
    document.getElementById('f_user').value   = '';
    document.getElementById('f_item').value   = '';
    state.filters = { action:'', user_id:'', item_id:'' };
    state.page = 1;
    loadLogs();
  }

  (function init(){
    state.page = 1;
    state.per_page = 20;
    loadLogs();
  })();
</script>
</body>
</html>
