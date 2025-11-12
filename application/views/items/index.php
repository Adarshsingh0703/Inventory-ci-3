<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Items (AJAX)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:1100px;margin:24px auto;padding:0 16px}
    a{color:#0b61d8;text-decoration:none}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top}
    .topbar a{margin-right:12px}
    form.search{margin:12px 0}
    input[type="text"],input[type="number"],textarea,select{padding:8px;width:100%;box-sizing:border-box}
    .row{display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:12px}
    .actions button{padding:6px 10px;cursor:pointer}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    @media (max-width: 640px){ .row{grid-template-columns:1fr} }

    /* Modal */
    .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center}
    .modal{background:#fff;max-width:720px;width:92%;border-radius:8px;padding:16px;box-shadow:0 8px 30px rgba(0,0,0,.2)}
    .modal header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
    .hidden{display:none}
  </style>
</head>
<body>
  <h2>Items (AJAX)</h2>

  <div class="topbar">
    <a href="<?= site_url('categories'); ?>">Categories</a>
    <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
    <a href="<?= site_url('auth/logout'); ?>">Logout</a>
  </div>

  <!-- Search -->
  <form class="search" onsubmit="event.preventDefault(); loadItems(qInput.value);">
    <input id="qInput" type="text" placeholder="Search by name or SKU">
    <button type="submit">Search</button>
  </form>

  <!-- Create form (AJAX) -->
  <h3>Create Item</h3>
  <div id="createMsg"></div>
  <form id="createForm" onsubmit="event.preventDefault(); onCreate();">
    <div class="row">
      <div>
        <label>Name</label>
        <input id="c_name" type="text" required>
      </div>
      <div>
        <label>SKU (optional)</label>
        <input id="c_sku" type="text">
      </div>
      <div>
        <label>Quantity</label>
        <input id="c_quantity" type="number" min="0" step="1" value="0" required>
      </div>
      <div>
        <label>Price</label>
        <input id="c_price" type="number" min="0" step="0.01" value="0.00" required>
      </div>
      <div style="grid-column:1 / -1">
        <label>Description</label>
        <textarea id="c_description"></textarea>
      </div>
      <div>
        <label>Category (ID, optional)</label>
        <input id="c_category_id" type="number" min="1" step="1" placeholder="e.g. 3">
      </div>
    </div>
    <div class="actions" style="margin-top:10px">
      <button type="submit">Create (AJAX)</button>
    </div>
  </form>

  <!-- Table -->
  <h3 style="margin-top:24px">Items</h3>
  <div id="listMsg"></div>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>SKU</th><th>Qty</th><th>Price</th><th>Description</th><th>Category ID</th><th>Actions</th>
      </tr>
    </thead>
    <tbody id="itemsBody">
      <tr><td colspan="8">Loading…</td></tr>
    </tbody>
  </table>

  <!-- Edit Modal -->
  <div id="editBackdrop" class="modal-backdrop">
    <div class="modal">
      <header>
        <h3>Edit Item</h3>
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
            <label>SKU</label>
            <input id="e_sku" type="text">
          </div>
          <div>
            <label>Quantity</label>
            <input id="e_quantity" type="number" min="0" step="1" required>
          </div>
          <div>
            <label>Price</label>
            <input id="e_price" type="number" min="0" step="0.01" required>
          </div>
          <div style="grid-column:1 / -1">
            <label>Description</label>
            <textarea id="e_description"></textarea>
          </div>
          <div>
            <label>Category (ID)</label>
            <input id="e_category_id" type="number" min="1" step="1">
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
      baseURL: '<?= site_url('api/items'); ?>',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    function setMsg(el, html, ok=false){
      el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : '';
    }
    function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

    function rowHtml(it){
      return `
        <tr id="row-${it.id}">
          <td>${it.id}</td>
          <td>${esc(it.name)}</td>
          <td>${esc(it.sku || '')}</td>
          <td>${it.quantity}</td>
          <td>${Number(it.price).toFixed(2)}</td>
          <td>${esc(it.description || '')}</td>
          <td>${it.category_id ?? ''}</td>
          <td>
            <button onclick="openEdit(${it.id})">Edit</button>
            <button onclick="onDelete(${it.id})">Delete</button>
          </td>
        </tr>
      `;
    }

    async function loadItems(q=''){
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      try{
        const url = q ? `?q=${encodeURIComponent(q)}` : '';
        const { data } = await api.get(url); // GET /api/items
        const items = data.items || [];
        const tbody = document.getElementById('itemsBody');
        if (!items.length){
          tbody.innerHTML = '<tr><td colspan="8">No items found.</td></tr>';
          return;
        }
        tbody.innerHTML = items.map(rowHtml).join('');
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
        sku: document.getElementById('c_sku').value.trim(),
        description: document.getElementById('c_description').value.trim(),
        quantity: document.getElementById('c_quantity').value,
        price: document.getElementById('c_price').value,
        category_id: document.getElementById('c_category_id').value
      };
      try{
        const { data } = await api.post('', payload); // POST /api/items
        setMsg(createMsg, 'Created (ID: '+data.id+')', true);
        document.getElementById('createForm').reset();
        loadItems(document.getElementById('qInput').value);
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
      if(!confirm('Delete item '+id+'?')) return;
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      try{
        await api.post('/'+id, { _method: 'DELETE' });
        const row = document.getElementById('row-'+id);
        if (row) row.remove();
        setMsg(listMsg, 'Deleted', true);
        if (document.querySelectorAll('#itemsBody tr').length === 0) {
          loadItems(document.getElementById('qInput').value);
        }
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Delete failed';
        setMsg(listMsg, msg, false);
      }
    }

    // ----- Edit flow -----
    function openEdit(id){
      // fetch one and open modal
      fetchOne(id).then(it=>{
        document.getElementById('e_id').value          = it.id;
        document.getElementById('e_name').value        = it.name || '';
        document.getElementById('e_sku').value         = it.sku || '';
        document.getElementById('e_quantity').value    = it.quantity ?? 0;
        document.getElementById('e_price').value       = (it.price!=null?Number(it.price).toFixed(2):'0.00');
        document.getElementById('e_description').value = it.description || '';
        document.getElementById('e_category_id').value = it.category_id || '';
        setMsg(document.getElementById('editMsg'), '');
        document.getElementById('editBackdrop').style.display='flex';
      }).catch(err=>{
        setMsg(document.getElementById('listMsg'), err?.response?.data?.error || 'Load failed', false);
      });
    }

    function closeEdit(){
      document.getElementById('editBackdrop').style.display='none';
    }

    async function fetchOne(id){
      const { data } = await api.get('/'+id); // GET /api/items/{id}
      return data;
    }

    async function onUpdate(){
      const editMsg = document.getElementById('editMsg');
      setMsg(editMsg, '');
      const id = document.getElementById('e_id').value;
      const payload = {
        _method: 'PUT',
        name: document.getElementById('e_name').value.trim(),
        sku: document.getElementById('e_sku').value.trim(),
        description: document.getElementById('e_description').value.trim(),
        quantity: document.getElementById('e_quantity').value,
        price: document.getElementById('e_price').value,
        category_id: document.getElementById('e_category_id').value
      };
      try{
        await api.post('/'+id, payload); // POST with _method=PUT
        // refresh the row in-place
        const it = await fetchOne(id);
        const row = document.getElementById('row-'+id);
        if (row) row.outerHTML = rowHtml(it);
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
    loadItems();
  </script>
</body>
</html>
