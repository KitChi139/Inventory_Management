<?php
// suppliers.php (frontend-first demo)
// This page expects inventory.css (your existing stylesheet) and jQuery to be available.
// It's frontend-only: demo rows are inserted with JS. Replace demo data with server-side fetch when ready.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Suppliers — Dashboard</title>

  <!-- Font Awesome & jQuery (your project already used these) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Reuse your existing styles -->
  <link rel="stylesheet" href="inventory.css" />

  <!-- Page-specific CSS -->
  <style>
    /* Ensure no horizontal scroll from this module */
    .content-grid, .table-wrap, .inventory-table { max-width: 100%; box-sizing: border-box; }
    .sup-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-bottom: 18px; }

    /* Suppliers table specifics */
    .suppliers-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .suppliers-table th, .suppliers-table td { padding: 12px 14px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 14px; text-align: left; }
    .suppliers-table thead th { background: #fafafa; color: #444; font-weight: 600; }

    /* Status badges */
    .badge { display: inline-block; padding: 6px 8px; border-radius: 999px; font-size: 12px; color: white; font-weight: 600; }
    .badge.active { background: #2ea44f; }
    .badge.inactive { background: #999; }
    .badge.pending { background: #f2b100; color: #fff; }

    /* action menu */
    .more-menu { min-width: 160px; right: 0; top: calc(100% + 8px); }
    .more-menu .menu-item { padding: 8px 10px; }

    /* Top filter/search */
    .table-controls { display:flex; align-items:center; gap:12px; justify-content:flex-end; }
    .search-wrapper input { min-width: 260px; }

    /* Modals (reuse your modal rules but ensure width) */
    .modal-content { width: 520px; max-width: 94%; }
    .modal .modal-content .field { margin-top:10px; }
    .modal .modal-content label { font-weight:600; display:block; margin-bottom:6px; }
    .modal .modal-content input, .modal .modal-content textarea, .modal .modal-content select {
      width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box;
    }
    .modal .modal-content textarea { min-height:120px; resize:vertical; }

    /* small helpers */
    .muted { color: #777; font-size: 13px; }
    .stat-small { font-size: 22px; font-weight:700; color:#222; }
    .rating { color: #f6b500; font-weight:700; }

    <style>
/* (minimal safe styles; keep your inventory.css) */
.status-ok { color:#12805c; font-weight:600; } .status-low { color:#b48a00; font-weight:600; } .status-out { color:#c5162e; font-weight:600; }
.quick-request { padding:14px; }
.qr-table { width:100%; border-collapse:collapse; margin-top:8px;} .qr-table th, .qr-table td { padding:8px; border-bottom:1px solid #eee; }
.qr-actions { display:flex; gap:8px; margin-top:12px; }
.modal { position:fixed; inset:0; display:none; background:rgba(0,0,0,.35); align-items:center; justify-content:center; z-index:1000; }
.modal .modal-content { background:#fff; width:min(560px, 92vw); border-radius:12px; padding:16px; }
.modal label { display:block; font-size:.9rem; margin-top:8px; }
.modal input[type="text"], .modal input[type="number"], .modal input[type="date"] { width:100%; padding:8px; border:1px solid #ddd; border-radius:8px; }
.filter-dropdown { position:absolute; background:#fff; border:1px solid #eee; border-radius:10px; padding:10px; right:0; top:44px; width:260px; box-shadow:0 8px 26px rgba(0,0,0,.15);}
.hidden { display:none; } .search-wrapper { display:flex; gap:8px; align-items:center; position:relative; }
.search-wrapper input[type="search"] { padding:8px 10px; border:1px solid #ddd; border-radius:8px; min-width:260px; }
.clear-btn { background:#f2f2f2; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; }


.alerts { margin-bottom:12px; }
.alert { padding:10px 12px; border-radius:8px; margin-bottom:8px; }
.alert.success { background:#e8fff4; color:#0d6b4d; }
.alert.error { background:#ffe9e9; color:#a10f1d; }
.alert.warning { background:#fff7e5; color:#8a6a00; }
</style>
  </style>
</head>
<body>

<aside class="sidebar" aria-label="Primary">
  <div class="profile">
    <div class="icon" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
    <button class="toggle" aria-expanded="true" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
  </div>
  <h3 class="title">Navigation</h3>
  <nav>
    <ul class="menu">
      <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li class="active"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div class="top-left">
      <h2>Suppliers</h2>
    </div>
    <div class="top-right">
      <button class="icon-btn" title="Notifications" aria-label="Notifications"><i class="fa-solid fa-bell bell"></i></button>
      <a href="#" class="btn add-supplier"><i class="fa-solid fa-plus"></i> Add Supplier</a>
    </div>
  </header>

  <!-- Cards -->
  <section class="cards sup-cards">
    <div class="card">
      <h4>Total Suppliers</h4>
      <p class="stat-small" id="stat-total">0</p>
    </div>
    <div class="card">
      <h4>Active Suppliers</h4>
      <p class="stat-small" id="stat-active">0</p>
    </div>
    <div class="card">
      <h4>Items Needs Restocking</h4>
      <p class="stat-small" id="stat-restock">0</p>
    </div>
    <div class="card">
      <h4>Average Rating</h4>
      <p class="stat-small rating" id="stat-rating">0.0</p>
    </div>
  </section>

  <section class="content-grid">
    <div class="table-panel box" style="min-width:0;">
      <div class="panel-top" style="align-items:flex-start;">
        <h4>Suppliers List</h4>

        <div class="table-controls">
          <div class="search-wrapper" style="position:relative;">
            <input id="supplier-search" type="search" placeholder="Search suppliers..." aria-label="Search suppliers" />
            <button class="filter-icon" id="supplier-filter-toggle" title="Filter" style="margin-left:6px;"><i class="fa-solid fa-filter"></i></button>

            <!-- Filter dropdown -->
            <div id="supplier-filter-dropdown" class="filter-dropdown hidden" style="right:0; top:42px; width:260px;">
              <label for="supplier-category">Category</label>
              <select id="supplier-category">
                <option value="">All</option>
              </select>
              <label for="supplier-status">Status</label>
              <select id="supplier-status">
                <option value="">All</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
              <button id="supplier-clear-filters" class="clear-btn">Clear Filters</button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap" style="overflow:auto;">
        <table class="suppliers-table" role="table" aria-label="Suppliers table">
          <thead>
            <tr>
              <th>Supplier</th>
              <th>Contact Person</th>
              <th>Contact No.</th>
              <th>Email</th>
              <th>Categories</th>
              <th>Rating</th>
              <th>Items</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="suppliers-tbody">
            <!-- JS populates rows -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Right panel (small info) -->
    <aside class="quick-request box" aria-label="Supplier quick info" style="width: var(--right-panel-width);">
      <h4>Selected Supplier</h4>
      <div id="sel-supplier" class="muted">No supplier selected</div>
      <div style="margin-top:12px;">
        <button class="btn primary" id="contact-sel" disabled><i class="fa-solid fa-envelope"></i> Contact</button>
        <button class="btn secondary" id="restock-sel" disabled style="margin-left:8px;"><i class="fa-solid fa-boxes-stacked"></i> Restock</button>
      </div>
    </aside>
  </section>
</main>

<!-- Add / Edit Supplier Modal -->
<div class="modal" id="supplierModal" style="display:none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3 id="supplierModalTitle">Add Supplier</h3>

    <form id="supplierForm">
      <input type="hidden" id="supplier-id" />
      <div class="field">
        <label for="supplier-name">Supplier Name</label>
        <input id="supplier-name" type="text" required />
      </div>
      <div class="field">
        <label for="supplier-contact">Contact Person</label>
        <input id="supplier-contact" type="text" />
      </div>
      <div class="field">
        <label for="supplier-phone">Phone</label>
        <input id="supplier-phone" type="text" />
      </div>
      <div class="field">
        <label for="supplier-email">Email</label>
        <input id="supplier-email" type="email" />
      </div>
      <div class="field">
        <label for="supplier-categories">Categories (comma separated)</label>
        <input id="supplier-categories" type="text" />
      </div>

      <div style="display:flex; gap:8px; margin-top:12px;">
        <button class="btn" id="save-supplier" type="submit">Save</button>
        <button class="btn secondary close-modal" type="button">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Contact Modal -->
<div class="modal" id="contactModal" style="display:none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3>Contact Supplier</h3>

    <div id="contact-supplier-info" class="muted"></div>

    <div class="field">
      <label for="contact-subject">Subject</label>
      <input id="contact-subject" type="text" />
    </div>
    <div class="field">
      <label for="contact-message">Message</label>
      <textarea id="contact-message"></textarea>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
      <button class="btn secondary close-modal">Cancel</button>
      <button class="btn" id="send-contact">Send Message</button>
    </div>
  </div>
</div>

<!-- Restock Modal -->
<div class="modal" id="restockModal" style="display:none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3>Restock Request</h3>

    <div id="restock-supplier-info" class="muted"></div>

    <div class="field">
      <label for="restock-subject">Subject</label>
      <input id="restock-subject" type="text" value="Low Stock Reorder Request" />
    </div>
    <div class="field">
      <label for="restock-message">Message</label>
      <textarea id="restock-message">Hello, we'd like to request re-order for items running low. Please provide availability and pricing for the items listed.</textarea>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
      <button class="btn secondary close-modal">Cancel</button>
      <button class="btn" id="send-restock">Send Request</button>
    </div>
  </div>
</div>

<!-- Page JS: demo data + interactions -->
<script>
$(function(){

  // Demo supplier data (replace with AJAX fetch later)
  const demoSuppliers = [
    { id: 1, name: "MedSupply Corp", contact: "Diane Cruz", phone: "+639271234567", email: "diane@example.com", categories: ["PPE","Pharma"], rating: 4.8, items: 12, status: "Active" },
    { id: 2, name: "HealthLine Inc", contact: "John Santos", phone: "+639101112131", email: "john@hl.com", categories: ["Devices"], rating: 4.2, items: 3, status: "Active" },
    { id: 3, name: "PharmaPlus", contact: "Maya Lopez", phone: "+639181234567", email: "maya@pharmaplus.com", categories: ["Pharma"], rating: 3.9, items: 0, status: "Inactive" }
  ];

  let suppliers = [...demoSuppliers];
  let selectedSupplierId = null;

  /* ------------------ render helpers ------------------ */
  function renderSuppliers() {
    const $tbody = $("#suppliers-tbody").empty();
    suppliers.forEach(s => {
      const cats = s.categories.join(", ");
      const ratingStars = `<span class="rating">${s.rating}</span>`;
      const statusBadge = `<span class="badge ${s.status.toLowerCase()==='active' ? 'active' : 'inactive'}">${s.status}</span>`;
      $tbody.append(`
        <tr data-id="${s.id}">
          <td>${escapeHtml(s.name)}</td>
          <td>${escapeHtml(s.contact)}</td>
          <td>${escapeHtml(s.phone)}</td>
          <td>${escapeHtml(s.email)}</td>
          <td>${escapeHtml(cats)}</td>
          <td>${ratingStars}</td>
          <td>${s.items}</td>
          <td>${statusBadge}</td>
          <td>
            <div class="action-wrap">
              <button class="icon-more"><i class="fa-solid fa-ellipsis"></i></button>
              <div class="more-menu">
                <button class="menu-item edit-supplier">Edit</button>
                <button class="menu-item contact-supplier">Contact</button>
                <button class="menu-item restock-supplier">Restock</button>
                <button class="menu-item danger delete-supplier">Delete</button>
              </div>
            </div>
          </td>
        </tr>
      `);
    });
    updateStats();
  }

  function updateStats(){
    $("#stat-total").text(suppliers.length);
    $("#stat-active").text(suppliers.filter(s => s.status === "Active").length);
    $("#stat-restock").text(suppliers.reduce((acc,s) => acc + (s.items < 5 ? 1 : 0), 0));
    const avg = suppliers.length ? (suppliers.reduce((a,b)=>a+b.rating,0)/suppliers.length).toFixed(1) : "0.0";
    $("#stat-rating").text(avg);
    // populate categories filter
    const cats = new Set();
    suppliers.forEach(s => s.categories.forEach(c => cats.add(c)));
    const $cat = $("#supplier-category").empty().append("<option value=''>All</option>");
    Array.from(cats).sort().forEach(c => $cat.append(`<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`));
  }

  function escapeHtml(text){ return (''+text).replace(/[&<>"'`]/g, ch=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;' })[ch]); }

  /* ------------------ interactions ------------------ */

  // Populate table at start
  renderSuppliers();

  // Sidebar navigation handlers (optional)
  $("#dashboard").click(()=>window.location.href="dashboard.php");
  $("#inventory").click(()=>window.location.href="inventory.php");
  $("#requests").click(()=>window.location.href="requests.php");

  // Action menu toggle
  $(document).on("click", ".icon-more", function(e){
    e.stopPropagation();
    const $menu = $(this).siblings(".more-menu");
    $(".more-menu").not($menu).hide();
    $menu.toggle();
  });
  $(document).click(()=>$(".more-menu").hide());

  // Row action handlers (edit/contact/restock/delete)
  $(document).on("click", ".edit-supplier", function(){
    const id = $(this).closest("tr").data("id");
    openSupplierModal("edit", id);
  });

  $(document).on("click", ".contact-supplier", function(){
    const id = $(this).closest("tr").data("id");
    openContactModal(id);
  });

  $(document).on("click", ".restock-supplier", function(){
    const id = $(this).closest("tr").data("id");
    openRestockModal(id);
  });

  $(document).on("click", ".delete-supplier", function(){
    const id = $(this).closest("tr").data("id");
    if(!confirm("Delete this supplier?")) return;
    suppliers = suppliers.filter(s=>s.id!==id);
    renderSuppliers();
  });

  // row click selects supplier for right panel
  $(document).on("click", ".suppliers-table tbody tr", function(e){
    const id = $(this).data("id");
    selectedSupplierId = id;
    const s = suppliers.find(x=>x.id===id);
    $("#sel-supplier").html(`<strong>${escapeHtml(s.name)}</strong><div class="muted">${escapeHtml(s.contact)} • ${escapeHtml(s.phone)}</div>`);
    $("#contact-sel, #restock-sel").prop("disabled", false);
  });

  // Add supplier button
  $(".add-supplier").click(function(e){
    e.preventDefault();
    openSupplierModal("add");
  });

  // Supplier modal open
  function openSupplierModal(mode, id){
    $("#supplierForm")[0].reset();
    $("#supplier-id").val("");
    $("#supplierModalTitle").text(mode==="add" ? "Add Supplier" : "Edit Supplier");
    if(mode==="edit"){
      const s = suppliers.find(x=>x.id===id);
      if(!s) return alert("Supplier not found");
      $("#supplier-id").val(s.id);
      $("#supplier-name").val(s.name);
      $("#supplier-contact").val(s.contact);
      $("#supplier-phone").val(s.phone);
      $("#supplier-email").val(s.email);
      $("#supplier-categories").val(s.categories.join(", "));
    }
    $("#supplierModal").show();
  }

  // Save supplier (frontend only)
  $("#supplierForm").on("submit", function(e){
    e.preventDefault();
    const id = $("#supplier-id").val();
    const obj = {
      id: id ? parseInt(id) : (suppliers.length ? Math.max(...suppliers.map(s=>s.id))+1 : 1),
      name: $("#supplier-name").val().trim(),
      contact: $("#supplier-contact").val().trim(),
      phone: $("#supplier-phone").val().trim(),
      email: $("#supplier-email").val().trim(),
      categories: $("#supplier-categories").val().split(",").map(x=>x.trim()).filter(Boolean),
      rating: 4.0,
      items: 0,
      status: "Active"
    };
    if(!obj.name) { alert("Supplier name required"); return; }

    if(id){
      suppliers = suppliers.map(s => s.id === obj.id ? {...s, ...obj} : s);
    } else {
      suppliers.push(obj);
    }
    renderSuppliers();
    $("#supplierModal").hide();
  });

  // Contact / Restock modals handling
  function openContactModal(id){
    const s = suppliers.find(x=>x.id===id);
    if(!s) return;
    $("#contact-supplier-info").html(`<strong>${escapeHtml(s.name)}</strong><div class="muted">${escapeHtml(s.contact)} • ${escapeHtml(s.email)} • ${escapeHtml(s.phone)}</div>`);
    $("#contact-subject").val("");
    $("#contact-message").val("");
    $("#contactModal").show();
  }

  function openRestockModal(id){
    const s = suppliers.find(x=>x.id===id);
    if(!s) return;
    $("#restock-supplier-info").html(`<strong>${escapeHtml(s.name)}</strong><div class="muted">${escapeHtml(s.contact)} • ${escapeHtml(s.email)}</div>`);
    $("#restock-subject").val("Low Stock Reorder Request");
    $("#restock-message").val(`Hello ${s.contact},\n\nWe need to restock items supplied by ${s.name}. Please provide availability and pricing.\n\nThanks.`);
    $("#restockModal").show();
  }

  // Right panel contact/restock buttons
  $("#contact-sel").click(()=> {
    if(!selectedSupplierId) return;
    openContactModal(selectedSupplierId);
  });
  $("#restock-sel").click(()=> {
    if(!selectedSupplierId) return;
    openRestockModal(selectedSupplierId);
  });

  // Send contact/restock (frontend: show success)
  $("#send-contact").click(function(){
    // replace with AJAX integration later
    alert("Message sent (demo).");
    $("#contactModal").hide();
  });
  $("#send-restock").click(function(){
    // replace with AJAX integration later
    alert("Restock request sent (demo).");
    $("#restockModal").hide();
  });

  // Close modal handlers
  $(".modal .close, .close-modal").click(function(){ $(this).closest(".modal").hide(); });

  // Filters & Search (simple)
  $("#supplier-filter-toggle").click(function(e){
    e.stopPropagation();
    $("#supplier-filter-dropdown").toggleClass("hidden");
  });
  $(document).on("click", function(e){
    if(!$(e.target).closest("#supplier-filter-dropdown, #supplier-filter-toggle").length){
      $("#supplier-filter-dropdown").addClass("hidden");
    }
  });

  $("#supplier-clear-filters").click(function(){
    $("#supplier-status, #supplier-category").val("");
    $("#supplier-search").val("");
    filterSuppliers();
    $("#supplier-filter-dropdown").addClass("hidden");
  });

  $("#supplier-category, #supplier-status").on("change", filterSuppliers);
  $("#supplier-search").on("input", filterSuppliers);

  function filterSuppliers(){
    const q = $("#supplier-search").val().toLowerCase();
    const cat = $("#supplier-category").val();
    const status = $("#supplier-status").val();
    $("#suppliers-tbody tr").each(function(){
      const name = $(this).find("td:nth-child(1)").text().toLowerCase();
      const categories = $(this).find("td:nth-child(5)").text();
      const st = $(this).find("td:nth-child(8)").text();
      let show = true;
      if(q && !name.includes(q) && !categories.toLowerCase().includes(q)) show = false;
      if(cat && !categories.split(",").map(s=>s.trim()).includes(cat)) show = false;
      if(status && !st.includes(status)) show = false;
      $(this).toggle(show);
    });
  }

  // small utility: close dropdown if ESC pressed
  $(document).keyup(function(e){ if(e.key === "Escape") $(".modal, .filter-dropdown").addClass("hidden").hide(); });

  // expose renderSuppliers for debugging
  window.renderSuppliers = renderSuppliers;

   // Sidebar navigation
  $("#dashboard").click(()=>window.location.href="dashboard.php");
  $("#inventory").click(()=>window.location.href="inventory.php");
  $("#request").click(()=>window.location.href="request_list.php");
  $("#low-stock").click(()=> window.location.href = "lowstock.php");
  $("#logout").click(()=>window.location.href="logout.php");
});
</script>

</body>
</html>