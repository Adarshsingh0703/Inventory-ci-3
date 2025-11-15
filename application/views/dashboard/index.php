<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600,700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root{
      --sidebar-width: 260px;
      --sidebar-collapsed: 72px;
      --primary: #0b61d8;
      --muted: #6b7280;
      --card-radius: 12px;
    }
    *{box-sizing:border-box}
    body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f5f7fb;margin:0}
    .app { display:flex; min-height:100vh; gap:20px; }
    /* SIDEBAR */
    .sidebar {
      width:var(--sidebar-width);
      background:#fff;
      border-right:1px solid #eceff4;
      padding:14px;
      transition:width .22s ease;
      display:flex;
      flex-direction:column;
    }
    .sidebar.collapsed { width:var(--sidebar-collapsed); }
    .brand { font-weight:700; color:var(--primary); font-size:18px; display:flex; gap:8px; align-items:center }
    .nav { margin-top:8px; display:flex; flex-direction:column; gap:6px; }
    .nav-link { color:#1f2937; border-radius:8px; padding:10px 10px; display:flex; gap:10px; align-items:center; text-decoration:none; }
    .nav-link:hover { background:#f1f5fb; text-decoration:none; }
    .nav-icon { width:36px; text-align:center; color:var(--muted) }
    .label { transition:opacity .18s }
    .sidebar.collapsed .label { opacity:0; pointer-events:none; width:0; display:inline-block; }
    .sidebar .bottom-spacer { margin-top:auto } /* keep bottom area empty (we removed quick buttons) */

    /* CONTENT */
    .content { flex:1; padding:24px; }
    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .toggle-btn { border:none; background:transparent; cursor:pointer; font-size:18px }
    /* Section card grid: responsive, fills width, no horizontal scroll */
    .section { margin-bottom:28px; }
    .section .heading { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
    .grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap:12px;
      align-items:start;
    }
    .card-item {
      background:#fff;
      border-radius:var(--card-radius);
      padding:14px;
      box-shadow:0 6px 18px rgba(10,20,50,0.04);
      min-height:110px;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }
    .card-item h5 { margin:0 0 8px; font-size:15px; }
    .card-sub { color:var(--muted); font-size:13px; margin-bottom:6px; }
    .view-all-card {
      background: linear-gradient(180deg, #f7fbff, #ffffff);
      border:1px dashed rgba(11,97,216,0.18);
      display:flex;
      align-items:center;
      justify-content:center;
      text-align:center;
      color:var(--primary);
      font-weight:600;
      cursor:pointer;
      transition:transform .12s ease;
      min-height:110px;
    }
    .view-all-card:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(11,97,216,0.06); }
    /* center manage buttons */
    .manage-actions { margin-top:36px; display:flex; justify-content:center; gap:18px; }
    .highlight { outline: 3px solid rgba(11,97,216,0.12); border-radius:10px; transition: outline .25s ease; }
    @media (max-width:800px){
      .sidebar{position:fixed;z-index:80;left:-100%;top:0;bottom:0;width:var(--sidebar-width)}
      .sidebar.open { left:0 }
      .app.mobile-open .content { filter: blur(1px) }
      .grid { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
    }
  </style>
</head>
<body>
  <div class="app" id="appRoot">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="brand">
          <i class="fa fa-boxes"></i>
          <span class="label">InventoryApp</span>
        </div>
        <div>
          <button class="toggle-btn" id="collapseBtn" title="Collapse sidebar"><i class="fa fa-angle-left"></i></button>
        </div>
      </div>

      <nav class="nav">
        <a href="<?= site_url('dashboard'); ?>" class="nav-link">
          <span class="nav-icon"><i class="fa fa-home"></i></span>
          <span class="label">Dashboard</span>
        </a>

        <!-- Items: now loads items section and filters to current user's items -->
        <a href="#" id="openItemsLink" class="nav-link">
          <span class="nav-icon"><i class="fa fa-th-large"></i></span>
          <span class="label">Items</span>
        </a>

        <!-- Categories: now loads categories section and filters to current user's categories -->
        <a href="#" id="openCategoriesLink" class="nav-link">
          <span class="nav-icon"><i class="fa fa-tags"></i></span>
          <span class="label">Categories</span>
        </a>

        <a href="<?= site_url('audit'); ?>" class="nav-link">
          <span class="nav-icon"><i class="fa fa-history"></i></span>
          <span class="label">Audit</span>
        </a>

        <a href="<?= site_url('auth/logout'); ?>" class="nav-link">
          <span class="nav-icon"><i class="fa fa-sign-out-alt"></i></span>
          <span class="label">Logout</span>
        </a>
      </nav>

      <div class="bottom-spacer"></div>
      <!-- intentionally left blank so collapsed state has no ugly buttons -->
    </aside>

    <!-- CONTENT -->
    <main class="content">
      <div class="topbar">
        <div>
          <button class="btn btn-sm btn-outline-secondary me-2 d-md-none" id="mobileMenuBtn"><i class="fa fa-bars"></i></button>
          <h4 style="display:inline-block;margin:0">Dashboard</h4>
          <div class="small text-muted ms-3" style="display:inline-block">Welcome, <?= html_escape($display_name); ?></div>
        </div>
        <div>
          <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('auth/logout'); ?>"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>

      <!-- Categories section -->
      <section class="section" id="categoriesSection">
        <div class="heading">
          <h5 style="margin:0">Categories</h5>
          <div class="small text-muted">Browse categories</div>
        </div>
        <div class="d-flex mb-2 gap-2">
          <button id="showCategoriesBtn" class="btn btn-sm btn-success">Show All Categories</button>
          <button id="refreshCats" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
        <div id="catsGrid" class="grid" aria-live="polite">
          <!-- JS will insert category cards here. Last tile will be "View all" -->
          <div class="text-muted">Loading categories…</div>
        </div>
      </section>

      <!-- Items section -->
      <section class="section" id="itemsSection">
        <div class="heading">
          <h5 style="margin:0">Items</h5>
          <div class="small text-muted">Some highlighted items</div>
        </div>
        <div class="d-flex mb-2 gap-2">
          <button id="showItemsBtn" class="btn btn-sm btn-primary">Show All Items</button>
          <button id="refreshItems" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
        <div id="itemsGrid" class="grid" aria-live="polite">
          <!-- JS will insert item cards here. Last tile will be "View all" -->
          <div class="text-muted">Loading items…</div>
        </div>
      </section>

      <!-- space and centered manage buttons -->
      <div style="height:28px"></div>
      <div class="manage-actions">
        <a href="<?= site_url('items'); ?>" class="btn btn-lg btn-primary">Manage Items</a>
        <a href="<?= site_url('categories'); ?>" class="btn btn-lg btn-outline-primary">Manage Categories</a>
      </div>

    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    // PHP -> JS: logged-in user ID (safe cast)
    const CURRENT_USER_ID = <?= (int) ($this->session && $this->session->userdata('user_id') ? $this->session->userdata('user_id') : 0); ?>;

    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapseBtn');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const appRoot = document.getElementById('appRoot');
    const catsGrid = document.getElementById('catsGrid');
    const itemsGrid = document.getElementById('itemsGrid');
    const openCategoriesLink = document.getElementById('openCategoriesLink');
    const openItemsLink = document.getElementById('openItemsLink');

    const showCategoriesBtn = document.getElementById('showCategoriesBtn');
    const refreshCatsBtn = document.getElementById('refreshCats');

    const showItemsBtn = document.getElementById('showItemsBtn');
    const refreshItemsBtn = document.getElementById('refreshItems');

    function saveSidebarState(collapsed){
      try { localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0'); } catch(e){}
    }
    function loadSidebarState(){
      try { return localStorage.getItem('sidebar_collapsed') === '1'; } catch(e){ return false; }
    }
    function updateCollapseIcon(){
      const icon = collapseBtn.querySelector('i');
      if (sidebar.classList.contains('collapsed')) {
        icon.className = 'fa fa-angle-right';
      } else {
        icon.className = 'fa fa-angle-left';
      }
    }

    (function initSidebar(){
      const collapsed = loadSidebarState();
      if (collapsed) sidebar.classList.add('collapsed');
      updateCollapseIcon();
    })();

    collapseBtn.addEventListener('click', function(){
      sidebar.classList.toggle('collapsed');
      saveSidebarState(sidebar.classList.contains('collapsed'));
      updateCollapseIcon();
    });

    mobileMenuBtn && mobileMenuBtn.addEventListener('click', function(){
      if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        appRoot.classList.remove('mobile-open');
      } else {
        sidebar.classList.add('open');
        appRoot.classList.add('mobile-open');
      }
    });

    // Helper: escape HTML
    function escapeHtml(s){
      if (s === null || s === undefined) return '';
      return String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
    }

    // --- CATEGORIES ---
    // loadCategories({ userId: number|null, per_page: number|null })
    async function loadCategories(opts = {}) {
      const userId = opts.userId || null;
      catsGrid.innerHTML = '<div class="text-muted">Loading categories…</div>';
      try {
        // include user_id filter if provided
        const params = new URLSearchParams();
        if (userId) params.set('user_id', userId);
        // backend may accept per_page or not; not required
        const url = '<?= site_url('api/categories'); ?>' + (params.toString() ? '?' + params.toString() : '');
        const res = await axios.get(url);
        const rows = (res.data && res.data.categories) ? res.data.categories : (Array.isArray(res.data) ? res.data : (res.data.categories ?? []));
        if (!rows || rows.length === 0) {
          // fallback samples
          const samples = [
            { id:0, name: 'Electronics', parent_id: null },
            { id:0, name: 'Clothing', parent_id: null },
            { id:0, name: 'Home & Kitchen', parent_id: null },
            { id:0, name: 'Sports', parent_id: null }
          ];
          renderCategories(samples, true);
          return;
        }
        renderCategories(rows, false);
      } catch (err) {
        console.error(err);
        const samples = [
          { id:0, name: 'Electronics', parent_id: null },
          { id:0, name: 'Clothing', parent_id: null },
        ];
        renderCategories(samples, true);
      }
    }

    function renderCategories(rows, isSample){
      const cards = rows.map(r => {
        const name = escapeHtml(r.name || r.title || 'Unnamed');
        const pid = r.parent_id ? ('Parent: ' + escapeHtml(String(r.parent_id))) : '';
        const id = r.id ? r.id : '';
        return `<div class="card-item">
                  <div>
                    <h5>${name}</h5>
                    <div class="card-sub">${pid}</div>
                  </div>
                  <div style="display:flex;justify-content:flex-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('category'); ?>/${id}">${ isSample ? 'View' : 'Open' }</a>
                  </div>
                </div>`;
      });

      catsGrid.innerHTML = cards.join('');
    }

    // --- ITEMS ---
    // loadItems({ userId: number|null, per_page: number|null })
    async function loadItems(opts = {}) {
      const userId = opts.userId || null;
      const per_page = opts.per_page || 12;
      itemsGrid.innerHTML = '<div class="text-muted">Loading items…</div>';
      try {
        const params = new URLSearchParams();
        params.set('page', 1);
        params.set('per_page', per_page);
        if (userId) params.set('user_id', userId);
        const url = '<?= site_url('api/items'); ?>' + (params.toString() ? '?' + params.toString() : '');
        const res = await axios.get(url);
        const data = res.data || {};
        const rows = data.items || (Array.isArray(res.data) ? res.data : []);
        if (!rows || rows.length === 0) {
          const samples = [
            { id:0, name: 'Wireless Earbuds', sku: 'EB-100', quantity: 20, price: '999.00' },
            { id:0, name: 'Running Shoes', sku: 'SH-45', quantity: 12, price: '1999.00' },
            { id:0, name: 'Coffee Maker', sku: 'CM-9', quantity: 5, price: '3499.00' },
            { id:0, name: 'Yoga Mat', sku: 'YM-3', quantity: 40, price: '499.00' }
          ];
          renderItems(samples, true);
          return;
        }
        renderItems(rows, false);
      } catch (err) {
        console.error(err);
        const samples = [
          { id:0, name: 'Wireless Earbuds', sku: 'EB-100', quantity: 20, price: '999.00' },
          { id:0, name: 'Running Shoes', sku: 'SH-45', quantity: 12, price: '1999.00' },
        ];
        renderItems(samples, true);
      }
    }

    function renderItems(rows, isSample){
      const cards = rows.map(it => {
        const name = escapeHtml(it.name || it.title || 'Unnamed');
        const sku = it.sku ? escapeHtml(it.sku) : '';
        const qty = it.quantity !== undefined ? escapeHtml(String(it.quantity)) : '';
        const price = it.price !== undefined ? escapeHtml(String(it.price)) : '';
        const id = it.id ? it.id : '';
        return `<div class="card-item">
                  <div>
                    <h5>${name}</h5>
                    <div class="card-sub">${sku} ${qty ? ' • QTY: '+qty : ''}</div>
                  </div>
                  <div style="display:flex;justify-content:flex-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('items'); ?>/${id}">${ isSample ? 'View' : 'Open' }</a>
                  </div>
                </div>`;
      });

      itemsGrid.innerHTML = cards.join('');
    }

    // Initialize = show everything (all items & categories)
    loadCategories();
    loadItems();

    // Sidebar Items link: load only current user's items and scroll/highlight
    openItemsLink.addEventListener('click', function(e){
      e.preventDefault();
      // if user not logged in, just load full items
      const uid = CURRENT_USER_ID && CURRENT_USER_ID > 0 ? CURRENT_USER_ID : null;
      loadItems({ userId: uid }).then(()=> {
        const el = document.getElementById('itemsSection');
        if (el) {
          el.scrollIntoView({behavior:'smooth', block:'start'});
          el.classList.add('highlight');
          setTimeout(()=> el.classList.remove('highlight'), 900);
        }
      }).catch(()=> {
        const el = document.getElementById('itemsSection');
        if (el) el.scrollIntoView({behavior:'smooth', block:'start'});
      });
      if (window.innerWidth < 900) { sidebar.classList.remove('open'); appRoot.classList.remove('mobile-open'); }
    });

    // Sidebar Categories link: load only current user's categories and scroll/highlight
    openCategoriesLink.addEventListener('click', function(e){
      e.preventDefault();
      const uid = CURRENT_USER_ID && CURRENT_USER_ID > 0 ? CURRENT_USER_ID : null;
      loadCategories({ userId: uid }).then(()=> {
        const el = document.getElementById('categoriesSection');
        if (el) {
          el.scrollIntoView({behavior:'smooth', block:'start'});
          el.classList.add('highlight');
          setTimeout(()=> el.classList.remove('highlight'), 900);
        }
      }).catch(()=> {
        const el = document.getElementById('categoriesSection');
        if (el) el.scrollIntoView({behavior:'smooth', block:'start'});
      });
      if (window.innerWidth < 900) { sidebar.classList.remove('open'); appRoot.classList.remove('mobile-open'); }
    });

    // Buttons: Show All vs Show My
    showCategoriesBtn.addEventListener('click', function(){ loadCategories({ userId: null }); });
    refreshCatsBtn.addEventListener('click', function(){ loadCategories(); });

    showItemsBtn.addEventListener('click', function(){ loadItems({ userId: null }); });
    refreshItemsBtn.addEventListener('click', function(){ loadItems(); });

  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
