<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Categories (AJAX)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:1000px;margin:24px auto;padding:0 16px}
    a{color:#0b61d8;text-decoration:none}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top}
    .topbar a{margin-right:12px}
    form.search{margin:12px 0}
    input[type="text"],input[type="number"],select{padding:8px;width:100%;box-sizing:border-box}
    .row{display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:12px}
    .actions button{padding:6px 10px;cursor:pointer}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    @media (max-width: 640px){ .row{grid-template-columns:1fr} }

    /* Modal */
    .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center}
    .modal{background:#fff;max-width:560px;width:92%;border-radius:8px;padding:16px;box-shadow:0 8px 30px rgba(0,0,0,.2)}
    .modal header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
  </style>
</head>
<body>
  <h2>Categories (AJAX)</h2>

  <div class="topbar">
    <a href="<?= site_url('items'); ?>">Items</a>
    <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
    <a href="<?= site_url('auth/logout'); ?>">Logout</a>
  </div>

  <!-- Create form (AJAX) -->
  <h3>Create Category</h3>
  <div id="createMsg"></div>
  <form id="createForm" onsubmit="event.preventDefault(); onCreate();">
    <div class="row">
      <div>
        <label>Name</label>
        <input id="c_name" type="text" required>
      </div>
      <div>
        <label>Parent ID (optional)</label>
        <input id="c_parent_id" type="number" min="1" step="1" placeholder="e.g. 3">
      </div>
    </div>
    <div class="actions" style="margin-top:10px">
      <button type="submit">Create (AJAX)</button>
    </div>
  </form>

  <!-- Table -->
  <h3 style="margin-top:24px">All Categories</h3>
  <div id="listMsg"></div>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Parent ID</th><th>Actions</th>
      </tr>
    </thead>
    <tbody id="catsBody">
      <tr><td colspan="4">Loading…</td></tr>
    </tbody>
  </table>

  <!-- Edit Modal -->
  <div id="editBackdrop" class="modal-backdrop">
    <div class="modal">
      <header>
        <h3>Edit Category</h3>
        <button type="button" onclick="closeEdit()">✕</button>
      </header>
      <div id="editMsg"></div>
      <form id="editForm" onsubmit="event.preventDefault(); onUpdate();">
        <input type="hidden" id="e_id">
        <div class="row">
          <div>
            <label>Name</label>
            <input id="e_name" type="text" required>
          </div>
          <div>
            <label>Parent ID (optional)</label>
            <input id="e_parent_id" type="number" min="1" step="1">
          </div>
        </div>
        <div class="actions" style="margin-top:10px">
          <button type="submit">Update</button>
          <button type="button" onclick="closeEdit()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const api = axios.create({
      baseURL: '<?= site_url('api/categories'); ?>',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    function setMsg(el, html, ok=false){
      el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : '';
    }
    function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

    function rowHtml(c){
      return `
        <tr id="row-${c.id}">
          <td>${c.id}</td>
          <td>${esc(c.name)}</td>
          <td>${c.parent_id ?? ''}</td>
          <td>
            <button onclick="openEdit(${c.id})">Edit</button>
            <button onclick="onDelete(${c.id})">Delete</button>
          </td>
        </tr>
      `;
    }

    async function loadCategories(){
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      try{
        const { data } = await api.get(''); // GET /api/categories
        const cats = data.categories || [];
        const tbody = document.getElementById('catsBody');
        if (!cats.length){
          tbody.innerHTML = '<tr><td colspan="4">No categories found.</td></tr>';
          return;
        }
        tbody.innerHTML = cats.map(rowHtml).join('');
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Failed to load';
        setMsg(listMsg, msg, false);
      }
    }

    async function onCreate(){
      const createMsg = document.getElementById('createMsg');
      setMsg(createMsg, '');
      const payload = {
        name: document.getElementById('c_name').value.trim(),
        parent_id: document.getElementById('c_parent_id').value
      };
      try{
        const { data } = await api.post('', payload); // POST /api/categories
        setMsg(createMsg, 'Created (ID: '+data.id+')', true);
        document.getElementById('createForm').reset();
        loadCategories();
      }catch(err){
        const res = err?.response;
        if (res && res.status === 422 && res.data?.errors){
          const first = Object.values(res.data.errors)[0];
          setMsg(createMsg, first || 'Validation error', false);
        }else{
          setMsg(createMsg, res?.data?.error || err?.message || 'Create failed', false);
        }
      }
    }

    async function onDelete(id){
      if(!confirm('Delete category '+id+'?')) return;
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      try{
        await api.post('/'+id, { _method: 'DELETE' });
        const row = document.getElementById('row-'+id);
        if (row) row.remove();
        setMsg(listMsg, 'Deleted', true);
        if (document.querySelectorAll('#catsBody tr').length === 0) {
          loadCategories();
        }
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Delete failed';
        setMsg(listMsg, msg, false);
      }
    }

    async function openEdit(id){
      try{
        const { data } = await api.get('/'+id); // GET /api/categories/{id}
        document.getElementById('e_id').value = data.id;
        document.getElementById('e_name').value = data.name || '';
        document.getElementById('e_parent_id').value = data.parent_id || '';
        setMsg(document.getElementById('editMsg'), '');
        document.getElementById('editBackdrop').style.display='flex';
      }catch(err){
        setMsg(document.getElementById('listMsg'), err?.response?.data?.error || 'Load failed', false);
      }
    }

    function closeEdit(){
      document.getElementById('editBackdrop').style.display='none';
    }

    async function onUpdate(){
      const editMsg = document.getElementById('editMsg');
      setMsg(editMsg, '');
      const id = document.getElementById('e_id').value;
      const payload = {
        _method: 'PUT',
        name: document.getElementById('e_name').value.trim(),
        parent_id: document.getElementById('e_parent_id').value
      };
      if (payload.parent_id !== '' && Number(payload.parent_id) === Number(id)) {
        setMsg(editMsg, 'Parent cannot be the same as the category.', false);
        return;
      }
      try{
        await api.post('/'+id, payload); // POST with _method=PUT
        // refresh this row
        const { data } = await api.get('/'+id);
        const row = document.getElementById('row-'+id);
        if (row) row.outerHTML = rowHtml(data);
        setMsg(document.getElementById('listMsg'), 'Updated', true);
        closeEdit();
      }catch(err){
        const res = err?.response;
        if (res && res.status === 422 && res.data?.errors){
          const first = Object.values(res.data.errors)[0];
          setMsg(editMsg, first || 'Validation error', false);
        }else{
          setMsg(editMsg, res?.data?.error || err?.message || 'Update failed', false);
        }
      }
    }

    // initial load
    loadCategories();
  </script>
</body>
</html>
