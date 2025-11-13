<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Audit Logs (Paginated & Readable)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--b:#0b61d8}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;max-width:1200px;margin:24px auto;padding:0 16px}
    a{color:var(--b);text-decoration:none}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    .btn{display:inline-block;background:var(--b);color:#fff;padding:8px 12px;border-radius:8px}
    .btn-outline{display:inline-block;border:1px solid var(--b);color:var(--b);padding:8px 12px;border-radius:8px}
    .filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}
    input,textarea,select{padding:8px;width:100%;box-sizing:border-box;border:1px solid #ddd;border-radius:6px}
    button{padding:8px 12px;cursor:pointer}
    table{border-collapse:collapse;width:100%;margin-top:12px;font-size:14px}
    th,td{border:1px solid #eee;padding:8px;text-align:left;vertical-align:top}
    th{background:#fafafa}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    .pager{display:flex;gap:8px;align-items:center;justify-content:center;margin-top:14px}
    .pagebtn{padding:6px 10px;border:1px solid #ddd;background:#fff;cursor:pointer;border-radius:6px}
    .pagebtn.active{background:#0b61d8;color:#fff;border-color:#0b61d8}
    .detail { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Roboto Mono", monospace; font-size:13px; color:#222; background:#f9f9fb; padding:8px; border-radius:6px; border:1px solid #eee; max-height:260px; overflow:auto }
    .panel { border:1px solid #eee; padding:10px; border-radius:8px; background:#fff; }
    .small { font-size:13px; color:#555 }
    .cols { display:flex; gap:12px; align-items:stretch }
    .col { flex:1; min-width:0 }
    .toggle { font-size:13px; padding:6px 8px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer }
    .nowrap{white-space:nowrap}
    @media (max-width:900px){ .filters{grid-template-columns:repeat(2,minmax(0,1fr))} .cols{flex-direction:column} }
  </style>
</head>
<body>
  <div class="top">
    <h2>Audit Logs</h2>
    <div>
      <a class="btn-outline" href="<?= site_url('dashboard'); ?>">Back to Dashboard</a>
      <a class="btn-outline" href="<?= site_url('auth/logout'); ?>">Logout</a>
    </div>
  </div>

  <div id="listMsg"></div>

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
    <div style="align-self:end">
      <button onclick="applyFilters()">Apply</button>
      <button onclick="clearFilters()">Clear</button>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:70px">ID</th>
        <th style="width:160px">Action</th>
        <th style="width:160px">User</th>
        <th style="width:120px">Item</th>
        <th>Details</th>
        <th style="width:160px">Created At</th>
      </tr>
    </thead>
    <tbody id="logsBody">
      <tr><td colspan="6">Loading…</td></tr>
    </tbody>
  </table>

  <div class="pager" id="pager"></div>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const api = axios.create({
      baseURL: '<?= site_url('api/audit'); ?>',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const state = { page: 1, per_page: 20, total: 0, total_pages: 0, filters: { action:'', user_id:'', item_id:'' } };

    function escapeHtml(s){
      if (s === null || s === undefined) return '';
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function prettyJsonHtml(obj){
      try {
        const txt = JSON.stringify(obj, null, 2);
        return '<pre class="detail">' + escapeHtml(txt) + '</pre>';
      } catch (e) {
        return '<pre class="detail">' + escapeHtml(String(obj)) + '</pre>';
      }
    }

    function renderDetails(detailsRaw) {
      if (!detailsRaw) return '<span class="small">-</span>';
      let parsed = null;
      if (typeof detailsRaw === 'object') {
        parsed = detailsRaw;
      } else {
        try { parsed = JSON.parse(detailsRaw); } catch (e) { parsed = null; }
      }

      if (parsed && typeof parsed === 'object') {
        if (parsed.before || parsed.after) {
          const before = parsed.before || {};
          const after  = parsed.after || {};
          return `<div class="panel">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                      <div class="small"><strong>Before / After</strong></div>
                      <button class="toggle" onclick="togglePanel(this)">Toggle</button>
                    </div>
                    <div class="cols">
                      <div class="col">
                        <div class="small"><strong>Before</strong></div>
                        ${prettyJsonHtml(before)}
                      </div>
                      <div class="col">
                        <div class="small"><strong>After</strong></div>
                        ${prettyJsonHtml(after)}
                      </div>
                    </div>
                  </div>`;
        }

        if (parsed.payload || parsed.deleted) {
          const key = parsed.payload ? 'payload' : 'deleted';
          return `<div class="panel">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                      <div class="small"><strong>${escapeHtml(key)}</strong></div>
                      <button class="toggle" onclick="togglePanel(this)">Toggle</button>
                    </div>
                    ${prettyJsonHtml(parsed[key])}
                  </div>`;
        }

        return `<div class="panel">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <div class="small"><strong>JSON</strong></div>
                    <button class="toggle" onclick="togglePanel(this)">Toggle</button>
                  </div>
                  ${prettyJsonHtml(parsed)}
                </div>`;
      }

      const escaped = escapeHtml(String(detailsRaw));
      const short = escaped.length > 400 ? escaped.slice(0,400) + '…' : escaped;
      const needsToggle = escaped.length > 400;
      return `<div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                  <div class="small"><strong>Text</strong></div>
                  ${needsToggle ? '<button class="toggle" onclick="togglePanel(this)">Toggle</button>' : ''}
                </div>
                <div class="detail">${short}${needsToggle ? '<div class="hidden-full" style="display:none">'+escaped+'</div>' : ''}</div>
              </div>`;
    }

    function togglePanel(btn){
      const panel = btn.closest('.panel');
      const pre = panel.querySelector('.detail');
      if (!pre) return;
      if (pre.style.maxHeight && pre.style.maxHeight !== 'none') {
        pre.style.maxHeight = 'none';
      } else {
        pre.style.maxHeight = '260px';
      }
      // also toggle hidden-full if present
      const hf = panel.querySelector('.hidden-full');
      if (hf) hf.style.display = hf.style.display === 'none' ? 'block' : 'none';
    }

    function rowHtml(r){
      const user = r.user_username ? `${escapeHtml(r.user_username)} (${r.user_id})` : (r.user_id ? r.user_id : '');
      const item = r.item_name ? `${escapeHtml(r.item_name)} (${r.item_id})` : (r.item_id ? r.item_id : '');
      const detailsHtml = renderDetails(r.details);
      return `
        <tr>
          <td class="nowrap">${r.id}</td>
          <td><code>${escapeHtml(r.action)}</code></td>
          <td>${user}</td>
          <td>${item}</td>
          <td>${detailsHtml}</td>
          <td class="nowrap small">${escapeHtml(r.created_at || '')}</td>
        </tr>
      `;
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

        state.total = pag.total;
        state.total_pages = pag.total_pages;
        state.per_page = pag.per_page;
        state.page = pag.page;

        const tbody = document.getElementById('logsBody');
        if (!logs.length){
          tbody.innerHTML = '<tr><td colspan="6">No logs found.</td></tr>';
          renderPager();
          return;
        }
        tbody.innerHTML = logs.map(rowHtml).join('');
        renderPager();
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Failed to load';
        document.getElementById('listMsg').innerHTML = '<div class="msg err">'+escapeHtml(msg)+'</div>';
      }
    }

    function renderPager(){
      const pager = document.getElementById('pager');
      pager.innerHTML = '';
      if (state.total_pages <= 1) return;

      // Prev
      const prev = document.createElement('button');
      prev.className = 'pagebtn';
      prev.textContent = 'Prev';
      prev.disabled = state.page <= 1;
      prev.onclick = () => goToPage(state.page - 1);
      pager.appendChild(prev);

      const windowSize = 5;
      let start = Math.max(1, state.page - Math.floor(windowSize/2));
      let end = Math.min(state.total_pages, start + windowSize - 1);
      if (end - start + 1 < windowSize) {
        start = Math.max(1, end - windowSize + 1);
      }

      for (let p = start; p <= end; p++){
        const btn = document.createElement('button');
        btn.className = 'pagebtn' + (p === state.page ? ' active' : '');
        btn.textContent = p;
        btn.onclick = () => goToPage(p);
        pager.appendChild(btn);
      }

      // Next
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
      document.getElementById('f_user').value = '';
      document.getElementById('f_item').value = '';
      state.filters = { action:'', user_id:'', item_id:'' };
      state.page = 1;
      loadLogs();
    }

    // initial load
    (function init(){
      state.page = 1;
      state.per_page = 20;
      loadLogs();
    })();
  </script>
</body>
</html>
