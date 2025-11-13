<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Items (AJAX + Pagination)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:sans-serif;max-width:1100px;margin:24px auto;padding:0 16px}
    a{color:#0b61d8;text-decoration:none}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top}
    .topbar a{margin-right:12px}
    form.search{margin:12px 0;display:flex;gap:8px}
    input[type="text"],input[type="number"],textarea,select{padding:8px;width:100%;box-sizing:border-box}
    .row{display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:12px}
    .actions button{padding:6px 10px;cursor:pointer}
    .msg{margin:10px 0;padding:8px;border-radius:6px}
    .err{background:#ffe6e6;color:#a40000}
    .ok{background:#e6ffef;color:#0b6a2b}
    .pager{display:flex;gap:8px;align-items:center;justify-content:center;margin-top:14px}
    .pagebtn{padding:6px 10px;border:1px solid #ddd;background:#fff;cursor:pointer;border-radius:6px}
    .pagebtn.active{background:#0b61d8;color:#fff;border-color:#0b61d8}
    @media (max-width: 640px){ .row{grid-template-columns:1fr} }
  </style>
</head>
<body>
  <h2>Items (AJAX + Pagination)</h2>

  <div class="topbar">
    <a href="<?= site_url('categories'); ?>">Categories</a>
    <a href="<?= site_url('dashboard'); ?>">Dashboard</a>
    <a href="<?= site_url('auth/logout'); ?>">Logout</a>
  </div>

  <!-- Search -->
  <form class="search" onsubmit="event.preventDefault(); goToPage(1);">
    <input id="qInput" type="text" placeholder="Search by name or SKU">
    <select id="perPageSelect" onchange="goToPage(1)">
      <option value="5">5 / page</option>
      <option value="10" selected>10 / page</option>
      <option value="20">20 / page</option>
      <option value="50">50 / page</option>
    </select>
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
        <th>#</th><th>ID</th><th>Name</th><th>SKU</th><th>Qty</th><th>Price</th><th>Category ID</th><th>Actions</th>
      </tr>
    </thead>
    <tbody id="itemsBody">
      <tr><td colspan="8">Loading…</td></tr>
    </tbody>
  </table>

  <!-- Pagination -->
  <div class="pager" id="pager"></div>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const api = axios.create({
      baseURL: '<?= site_url('api/items'); ?>',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    // client state
    const state = { page: 1, per_page: 10, total: 0, total_pages: 0, q: '' };

    function setMsg(el, html, ok=false){ el.innerHTML = html ? '<div class="msg '+(ok?'ok':'err')+'">'+html+'</div>' : ''; }
    function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

    function renderRow(serial, it){
      return `
        <tr id="row-${it.id}">
          <td>${serial}</td>
          <td>${it.id}</td>
          <td>${esc(it.name)}</td>
          <td>${esc(it.sku || '')}</td>
          <td>${it.quantity}</td>
          <td>${Number(it.price).toFixed(2)}</td>
          <td>${it.category_id ?? ''}</td>
          <td>
            <button onclick="openEdit(${it.id})">Edit</button>
            <button onclick="onDelete(${it.id})">Delete</button>
          </td>
        </tr>
      `;
    }

    async function loadItems(){
      const listMsg = document.getElementById('listMsg');
      setMsg(listMsg, '');
      try{
        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        params.set('page', state.page);
        params.set('per_page', state.per_page);

        const { data } = await api.get('?'+params.toString());
        const items = data.items || [];
        const pag = data.pagination || { total:0, page:state.page, per_page:state.per_page, total_pages:0, offset:0 };

        state.total = pag.total;
        state.total_pages = pag.total_pages;
        state.per_page = pag.per_page;
        state.page = pag.page;

        const tbody = document.getElementById('itemsBody');
        if (!items.length){
          tbody.innerHTML = '<tr><td colspan="8">No items found.</td></tr>';
          renderPager();
          return;
        }

        // serial number: start index + i
        const startSerial = pag.offset + 1;
        tbody.innerHTML = items.map((it, idx) => renderRow(startSerial + idx, it)).join('');
        renderPager();
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Failed to load';
        setMsg(listMsg, msg, false);
      }
    }

    function renderPager(){
      const pager = document.getElementById('pager');
      pager.innerHTML = '';
      if (state.total_pages <= 1) return;

      // Prev button
      const prev = document.createElement('button');
      prev.className = 'pagebtn';
      prev.textContent = 'Prev';
      prev.disabled = state.page <= 1;
      prev.onclick = () => goToPage(state.page - 1);
      pager.appendChild(prev);

      // page numbers (show window)
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

      // Next button
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
      state.q = document.getElementById('qInput').value.trim();
      state.per_page = parseInt(document.getElementById('perPageSelect').value, 10) || 10;
      loadItems();
    }

    // Create / Delete / Edit functions (same as previous)
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
        const { data } = await api.post('', payload);
        setMsg(createMsg, 'Created (ID: '+data.id+')', true);
        document.getElementById('createForm').reset();
        // jump to last page where the new item would appear (use count to compute)
        // simple approach: reload current page
        loadItems();
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
        setMsg(listMsg, 'Deleted', true);
        // reload current page (could adjust to remove row only)
        loadItems();
      }catch(err){
        const msg = err?.response?.data?.error || err?.message || 'Delete failed';
        setMsg(listMsg, msg, false);
      }
    }

    // Edit modal functions — reuse earlier modal code if you have it
    // If you don't have modal code on this page, you can re-add the Edit modal from the previous full file and the functions openEdit(), closeEdit(), fetchOne(), onUpdate()
    // For brevity, here's a minimal edit flow using prompt (quick):
    async function openEdit(id){
      try {
        const { data } = await api.get('/'+id);
        const name = prompt('Name', data.name);
        if (name === null) return;
        const sku = prompt('SKU', data.sku || '');
        if (sku === null) return;
        const quantity = prompt('Quantity', data.quantity);
        if (quantity === null) return;
        const price = prompt('Price', data.price);
        if (price === null) return;
        const description = prompt('Description', data.description || '');
        if (description === null) return;
        const category_id = prompt('Category ID (blank for none)', data.category_id || '');
        if (category_id === null) return;

        const payload = {
          _method: 'PUT',
          name: name.trim(),
          sku: sku.trim(),
          description: description.trim(),
          quantity: Number(quantity),
          price: Number(price),
          category_id: category_id === '' ? '' : Number(category_id)
        };
        await api.post('/'+id, payload);
        loadItems();
      } catch (err) {
        alert(err?.response?.data?.error || err?.message || 'Update failed');
      }
    }

    // initial
    (function init(){
      // set per_page select to state default
      document.getElementById('perPageSelect').value = state.per_page;
      goToPage(1);
    })();
  </script>
</body>
</html>
