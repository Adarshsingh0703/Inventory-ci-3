<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Audit Logs (AJAX)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--b:#0b61d8}
    body{font-family:sans-serif;max-width:1200px;margin:24px auto;padding:0 16px}
    a{color:var(--b);text-decoration:none}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    .btn{display:inline-block;background:var(--b);color:#fff;padding:8px 12px;border-radius:8px}
    .btn-outline{display:inline-block;border:1px solid var(--b);color:var(--b);padding:8px 12px;border-radius:8px}
    .filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}
    input{padding:8px;width:100%;box-sizing:border-box}
    button{padding:8px 12px;cursor:pointer}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top;font-size:14px}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    .pager{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:10px}
    .nowrap{white-space:nowrap}
    @media (max-width: 900px){ .filters{grid-template-columns:repeat(2,minmax(0,1fr))} }
    @media (max-width: 520px){ .filters{grid-template-columns:1fr} }
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

  <!-- Filters -->
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
    <div class="nowrap" style="align-self:end">
      <button onclick="applyFilters()">Apply</button>
      <button onclick="clearFilters()">Clear</button>
    </div>
  </div>

  <!-- Table -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Action</th>
        <th>User</th>
        <th>Item</th>
        <th>Details</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody id="logsBody">
      <tr><td colspan="6">Loading…</td></tr>
    </tbody>
  </table>

  <!-- Pagination -->
  <div class="pager">
    <button onclick="prevPage()">Prev</button>
    <span id="pageInfo"></span>
    <button onclick="nextPage()">Next</button>
  </div>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const api = axios.create({
      baseURL: '<?= site_url('api/audit'); ?>',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const state = { limit: 20, offset: 0, filters: { action:'', user_id:'', item_id:'' } };

    function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
    function setMsg(el, html, ok=false){ el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : ''; }

    function rowHtml(r){
      return `
        <tr>
          <td>${r.id}</td>
          <td><code>${esc(r.action)}</code></td>
          <td>${r.user_id ?? ''} ${r.user_username ? '('+esc(r.user_username)+')' : ''}</td>
          <td>${r.item_id ?? ''} ${r.item_name ? '('+esc(r.item_name)+')' : ''}</td>
          <td style="max-width:480px">${esc(r.details || '')}</td>
          <td class="nowrap">${esc(r.created_at || '')}</td>
        </tr>
      `;
    }

    async function loadLogs(){
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      const params = new URLSearchParams();
      if (state.filters.action) params.set('action', state.filters.action);
      if (state.filters.user_id) params.set('user_id', state.filters.user_id);
      if (state.filters.item_id) params.set('item_id', state.filters.item_id);
      params.set('limit', state.limit);
      params.set('offset', state.offset);

      try{
        const { data } = await api.get('?'+params.toString());
        const logs = data.logs || [];
        const tbody = document.getElementById('logsBody');
        if (!logs.length){
          tbody.innerHTML = '<tr><td colspan="6">No logs found.</td></tr>';
        } else {
          tbody.innerHTML = logs.map(rowHtml).join('');
        }
        document.getElementById('pageInfo').textContent =
          `Showing ${logs.length} (offset ${state.offset})`;
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Failed to load';
        setMsg(listMsg, msg, false);
      }
    }

    function applyFilters(){
      state.filters.action  = document.getElementById('f_action').value.trim();
      state.filters.user_id = document.getElementById('f_user').value;
      state.filters.item_id = document.getElementById('f_item').value;
      state.offset = 0;
      loadLogs();
    }
    function clearFilters(){
      document.getElementById('f_action').value = '';
      document.getElementById('f_user').value = '';
      document.getElementById('f_item').value = '';
      state.filters = { action:'', user_id:'', item_id:'' };
      state.offset = 0;
      loadLogs();
    }
    function nextPage(){ state.offset += state.limit; loadLogs(); }
    function prevPage(){ state.offset = Math.max(0, state.offset - state.limit); loadLogs(); }

    // initial load
    loadLogs();
  </script>
</body>
</html>
