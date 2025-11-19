<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Inventory — Categories</title>
  <style>
    :root{ --bg:#f6f8fb; --card:#ffffff; --muted:#6b7280; --accent:#2563eb; --accent-2:#0b61d8; --danger:#ef4444; --shadow: 0 8px 28px rgba(20,20,50,0.06); font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    html,body{height:100%;margin:0;background:var(--bg);color:#111827}
    .app{display:flex;min-height:100vh}
    .sidebar{width:260px;background:linear-gradient(180deg,#0f172a,#08122a);color:#fff;padding:22px 18px;box-shadow: rgba(2,6,23,0.2) 0 3px 12px;}
    .brand{font-weight:700;font-size:18px;margin-bottom:12px;display:flex;align-items:center;gap:10px}
    .brand .logo{width:36px;height:36px;border-radius:8px;background:linear-gradient(90deg,#2563eb,#7c3aed);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    .muted{color:var(--muted)}
    .nav{margin-top:18px}
    .nav a{display:block;padding:10px 12px;border-radius:8px;color:rgba(255,255,255,0.9);text-decoration:none;margin-bottom:6px}
    .nav a.active{background:rgba(255,255,255,0.06)}

    .main{flex:1;padding:22px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
    .card{background:var(--card);border-radius:12px;padding:16px;box-shadow:var(--shadow);margin-bottom:16px}
    .toolbar{display:flex;gap:12px;align-items:center}
    .search{display:flex;gap:8px;align-items:center}
    input[type="text"],input[type="number"],textarea,select{padding:10px;border:1px solid #e6e9ef;border-radius:8px;width:100%;box-sizing:border-box}
    button{background:var(--accent);color:#fff;border:0;padding:10px 12px;border-radius:8px;cursor:pointer}
    button.ghost{background:transparent;color:var(--accent);border:1px solid rgba(37,99,235,0.12)}
    button.warn{background:var(--danger)}
    .flex{display:flex;gap:8px;align-items:center}
    .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
    @media (max-width:820px){ .sidebar{display:none} .grid-2{grid-template-columns:1fr} }

    table{border-collapse:collapse;width:100%;margin-top:12px;border-radius:8px;overflow:hidden}
    thead{background:#f3f6fb}
    th,td{padding:12px 14px;text-align:left;border-bottom:1px solid #f1f3f5}
    tbody tr:hover{background: #fbfdff}

    .pager{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:14px}
    .pagebtn{padding:6px 10px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;border-radius:6px}
    .pagebtn.active{background:var(--accent-2);color:#fff;border-color:var(--accent-2)}

    .msg{margin:10px 0;padding:10px;border-radius:8px}
    .err{background:#fff1f2;color:#9f1239;border:1px solid rgba(239,68,68,0.08)}
    .ok{background:#ecfdf5;color:#065f46;border:1px solid rgba(16,185,129,0.08)}

    .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,0.45);display:none;align-items:center;justify-content:center;z-index:60}
    .modal{background:var(--card);max-width:640px;width:94%;border-radius:12px;padding:18px;box-shadow:var(--shadow)}
    .mini{font-size:13px;color:var(--muted)}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    .label{font-weight:600;margin-bottom:6px;display:block}
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand"><span class="logo">IA</span> Inventory App</div>
      <div class="mini muted">Manage items, categories & stock</div>
      <nav class="nav" aria-label="Main navigation">
        <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
        <a href="<?= site_url('categories'); ?>" class="active">Categories</a>
        <a href="<?= site_url('items'); ?>">Items</a>
        <a href="<?= site_url('audit'); ?>">Audit Logs</a>
        <a href="<?= site_url('auth/logout'); ?>">Logout</a>
      </nav>
    </aside>

    <main class="main">
      <div class="topbar">
        <div>
          <h2 style="margin:0 0 6px 0">Categories</h2>
          <div class="mini muted">Create and manage product categories</div>
        </div>

        <div class="toolbar">
          <div class="search">
            <input id="qInput" type="text" placeholder="Search categories..." oninput="debounced()" style="min-width:220px" />
            <select id="perPageSelect" onchange="goToPage(1)">
              <option value="10">10 / page</option>
              <option value="25">25 / page</option>
              <option value="50">50 / page</option>
            </select>
            <button class="ghost" onclick="clearSearch()">Clear</button>
          </div>
          <button onclick="openCreateModal()">+ Add Category</button>
        </div>
      </div>

      <section class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
          <div class="mini muted">Showing <strong id="countDisplay">0</strong> categories</div>
          <div class="mini muted" style="margin-left:auto">Page <span id="pageDisplay">1</span></div>
        </div>

        <div id="listMsg"></div>

        <table aria-live="polite">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th style="width:80px">ID</th>
              <th>Name</th>
              <th style="width:120px">Parent</th>
              <th style="width:170px">Actions</th>
            </tr>
          </thead>
          <tbody id="catsBody">
            <tr><td colspan="5">Loading…</td></tr>
          </tbody>
        </table>

        <div class="pager" id="pager"></div>
      </section>
    </main>
  </div>

  <!-- Create / Edit Modal -->
  <div id="modalBackdrop" class="modal-backdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <h3 id="modalTitle">Add Category</h3>
      <div id="createMsg" style="margin-top:8px"></div>

      <form id="categoryForm" onsubmit="return onCreateOrUpdate(event)">
        <input type="hidden" id="cat_id" />
        <div class="grid-2" style="margin-top:12px">
          <div>
            <label class="label">Name</label>
            <input id="c_name" type="text" required />
          </div>
          <div>
            <label class="label">Parent (ID, optional)</label>
            <input id="c_parent_id" type="number" min="1" step="1" placeholder="e.g. 3" />
          </div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" onclick="closeModal()">Cancel</button>
          <button id="saveBtn" type="submit">Save Category</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const api = axios.create({ baseURL: '<?= site_url('api/categories'); ?>', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const state = { page:1, per_page:10, total:0, total_pages:0, q:'', offset:0 };
    let debounceId = null;

    function setMsg(el, html, ok=false){ el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : ''; }
    function esc(s){ return (s==null?'':String(s)).replace(/[&<>'\"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','\"':'&quot;'}[m])); }

    function rowHtml(c, serial){
      return `\n        <tr id="row-${c.id}">\n          <td>${serial}</td>\n          <td>${c.id}</td>\n          <td><strong>${esc(c.name)}</strong></td>\n          <td class="mini">${c.parent_name ? esc(c.parent_name) + ' (ID:'+c.parent_id+')' : (c.parent_id || '')}</td>\n          <td>\n            <button class="ghost" onclick="openEdit(${c.id})">Edit</button>\n            <button class="warn" onclick="onDelete(${c.id})">Delete</button>\n          </td>\n        </tr>\n      `;
    }

    async function loadCategories(){
      const listMsg = document.getElementById('listMsg'); setMsg(listMsg,'');
      try{
        const params = new URLSearchParams(); if(state.q) params.set('q', state.q); params.set('page', state.page); params.set('per_page', state.per_page);
        const { data } = await api.get('?'+params.toString());
        const cats = data.categories || data.data || [];
        const pag = data.pagination || data.pagination || { total:cats.length, page:state.page, per_page:state.per_page, total_pages:1, offset:0 };

        state.total = pag.total || cats.length; state.total_pages = pag.total_pages || 1; state.per_page = pag.per_page || state.per_page; state.page = pag.page || state.page; state.offset = pag.offset || ((state.page-1)*state.per_page);

        const tbody = document.getElementById('catsBody');
        if(!cats.length){ tbody.innerHTML = '<tr><td colspan="5" class="mini muted">No categories found.</td></tr>'; renderPager(); document.getElementById('countDisplay').innerText = state.total; return; }

        const startSerial = state.offset + 1;
        tbody.innerHTML = cats.map((c, idx) => rowHtml(c, startSerial + idx)).join('');
        document.getElementById('countDisplay').innerText = state.total;
        document.getElementById('pageDisplay').innerText = state.page + ' / ' + (state.total_pages || 1);
        renderPager();
      }catch(err){ setMsg(document.getElementById('listMsg'), err?.response?.data?.error || err?.message || 'Failed to load', false); }
    }

    function renderPager(){
      const pager = document.getElementById('pager'); pager.innerHTML = '';
      if(state.total_pages <= 1) return;
      const addBtn = (text, disabled, cb, cls='') => { const b = document.createElement('button'); b.className = 'pagebtn '+cls; b.textContent = text; b.disabled = !!disabled; b.onclick = cb; return b; };
      pager.appendChild(addBtn('Prev', state.page<=1, ()=>goToPage(state.page-1)));
      const windowSize = 5; let start = Math.max(1, state.page - Math.floor(windowSize/2)); let end = Math.min(state.total_pages, start + windowSize -1); if(end - start +1 < windowSize) start = Math.max(1, end - windowSize +1);
      for(let p=start;p<=end;p++){ const btn = addBtn(p, false, ()=>goToPage(p), p===state.page? 'active':''); pager.appendChild(btn); }
      pager.appendChild(addBtn('Next', state.page>=state.total_pages, ()=>goToPage(state.page+1)));
    }

    function goToPage(p){ if(p<1) p=1; if(state.total_pages && p>state.total_pages) p=state.total_pages; state.page=p; state.q = document.getElementById('qInput').value.trim(); state.per_page = parseInt(document.getElementById('perPageSelect').value,10)||10; loadCategories(); }
    function debounced(){ clearTimeout(debounceId); debounceId = setTimeout(()=>{ goToPage(1); }, 350); }
    function clearSearch(){ document.getElementById('qInput').value=''; goToPage(1); }

    function showModal(show=true){ const b = document.getElementById('modalBackdrop'); b.style.display = show ? 'flex' : 'none'; if(show) document.getElementById('c_name').focus(); }
    function openCreateModal(){ document.getElementById('modalTitle').innerText='Add Category'; document.getElementById('categoryForm').reset(); document.getElementById('cat_id').value=''; setMsg(document.getElementById('createMsg'),''); showModal(true); }
    function openEditModalData(cat){ document.getElementById('modalTitle').innerText='Edit Category'; document.getElementById('cat_id').value = cat.id; document.getElementById('c_name').value = cat.name || ''; document.getElementById('c_parent_id').value = cat.parent_id || ''; setMsg(document.getElementById('createMsg'),''); showModal(true); }
    function closeModal(){ showModal(false); }

    async function onCreateOrUpdate(e){ e.preventDefault(); const id = document.getElementById('cat_id').value; const payload = { name: document.getElementById('c_name').value.trim(), parent_id: document.getElementById('c_parent_id').value || '' };
      if(payload.parent_id !== '' && id !== '' && Number(payload.parent_id) === Number(id)){ setMsg(document.getElementById('createMsg'), 'Parent cannot be the same as the category.', false); return false; }
      try{
        if(id){ await api.post('/'+id, Object.assign({_method:'PUT'}, payload)); setMsg(document.getElementById('createMsg'),'Updated', true); }
        else{ const { data } = await api.post('', payload); setMsg(document.getElementById('createMsg'),'Created (ID: '+(data.id||'')+')', true); }
        closeModal(); loadCategories();
      }catch(err){ const res = err?.response; if(res && res.status===422 && res.data?.errors){ const first = Object.values(res.data.errors)[0]; setMsg(document.getElementById('createMsg'), first || 'Validation error', false); } else setMsg(document.getElementById('createMsg'), res?.data?.error || err?.message || 'Save failed', false); }
      return false; }

    async function openEdit(id){ try{ const { data } = await api.get('/'+id); openEditModalData(data); }catch(err){ setMsg(document.getElementById('listMsg'), err?.response?.data?.error || 'Load failed', false); } }

    async function onDelete(id){ if(!confirm('Delete category '+id+'?')) return; try{ await api.post('/'+id, {_method:'DELETE'}); const row = document.getElementById('row-'+id); if(row) row.remove(); setMsg(document.getElementById('listMsg'),'Deleted', true); if(document.querySelectorAll('#catsBody tr').length === 0) loadCategories(); }catch(err){ setMsg(document.getElementById('listMsg'), err?.response?.data?.error || err?.message || 'Delete failed', false); } }

    (function init(){ document.getElementById('perPageSelect').value = state.per_page; document.getElementById('modalBackdrop').addEventListener('click', (ev)=>{ if(ev.target === ev.currentTarget) closeModal(); }); goToPage(1); })();
  </script>
</body>
</html>
