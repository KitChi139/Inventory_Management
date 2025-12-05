
let requestsData = {
    pending: [
        { id: 'REQ-001', date: '2025-04-10', items: ['Face Mask | qty: 50 boxes', 'Medical Gloves | qty: 30 boxes'], amounts: ['₱25,000', '₱20,000'], total: '₱45,000' }
    ],
    declined: [
        { id: 'REQ-005', date: '2025-04-03', items: ['Syringes | qty: 200 pieces'], reason: 'Stock not sufficient', total: '₱50,000' }
    ],
    approved: [
        { id: 'REQ-002', date: '2025-04-08', items: ['Sanitizers | qty: 50 bottles', 'Syringes | qty: 100 pieces'], amounts: ['₱22,500', '₱25,000'], total: '₱47,500' }
    ],
    completed: [
        { id: 'REQ-003', date: '2025-04-05', items: ['Thermometers | qty: 25 units', 'Face Mask | qty: 30 boxes'], delivered: '2025-04-08', total: '₱36,250' }
    ]
};

let inventoryData = [
    { name: 'Face Mask', stock: '500 pcs', price: 500, status: 'In Stock' },
    { name: 'Medical Gloves', stock: '320 boxes', price: 650, status: 'In Stock' },
    { name: 'Syringes', stock: '200 pieces', price: 250, status: 'In Stock' },
    { name: 'Sanitizers', stock: '150 bottles', price: 450, status: 'In Stock' },
    { name: 'Thermometers', stock: '75 units', price: 850, status: 'Low Stock' }
];

let companyProfile = {
    address: '1456 Industrial Avenue, Metro City, Philippines',
    phone: '+63 (2) 8456-7890',
    email: 'support@medisync.com.ph',
    website: 'www.medisync.com.ph',
    regional: 'Luzon: Quezon City, Metro Manila | Visayas: Cebu City | Mindanao: Davao City',
    mission: '"To deliver reliable, innovative, and high-quality medical supplies that safeguard lives and support healthier communities across the Philippines."',
    history: `<p>MediSync is a trusted supplier of medical and healthcare essentials in the Philippines, dedicated to serving hospitals, clinics, and healthcare providers for over two decades. Established in 2001, the company began as a small local distributor and has since expanded into a nationwide brand recognized for its reliability, innovation, and commitment to quality.</p>
        <p>Over the years, MediSync has continuously adapted to the evolving needs of the healthcare industry. From protective equipment and diagnostic tools to sanitization and emergency supplies, our products are designed to meet the highest standards of safety and performance. Our strong partnerships with healthcare providers nationwide stand as proof of our mission to safeguard lives and promote healthier communities.</p>`
};

let notificationsData = [
    { id: 1, title: 'Request REQ-001 Received', message: 'New request received from Inventory Manager', time: '2 hours ago', unread: true },
    { id: 2, title: 'Request REQ-002 Approved', message: 'Request has been successfully approved', time: '5 hours ago', unread: true },
    { id: 3, title: 'Request REQ-003 Completed', message: 'Delivery completed for REQ-003', time: '1 day ago', unread: true },
    { id: 4, title: 'New Message from Hospital Admin', message: 'Stock availability check request', time: '2 days ago', unread: false }
];

const messageData = {
    1: {
        subject: 'Order Inquiry - REQ-001',
        sender: 'Inventory Manager',
        date: '2025-04-10',
        content: 'Hello, I would like to inquire about the availability of Face Masks and Medical Gloves for REQ-001.'
    },
    2: {
        subject: 'Schedule Update - REQ-002',
        sender: 'Procurement Team',
        date: '2025-04-08',
        content: 'We need to update the delivery date for REQ-002. Please confirm if you can accommodate the new schedule.'
    },
    3: {
        subject: 'Stock Availability Check',
        sender: 'Hospital Admin',
        date: '2025-04-07',
        content: 'Please provide current stock levels for Syringes, Sanitizers, and Thermometers.'
    }
};

document.addEventListener('DOMContentLoaded', () => {
    populateInventoryTable();
    updateCompanyProfileDisplay();
    setupNotificationBell();
    wireTabLinks();
});

function wireTabLinks() {
    document.querySelectorAll('.tab-link').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            switchTab(tab);
        });
    });
}

function switchTab(tab) {
    document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

    const targetBtn = document.querySelector(`[data-tab="${tab}"]`);
    if (targetBtn) targetBtn.classList.add('active');

    const sectionId = tab + '-section';
    const section = document.getElementById(sectionId);
    if (section) section.classList.add('active');
}

function populateInventoryTable() {
    const tbody = document.getElementById('inventoryTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    inventoryData.forEach(item => {
        let statusClass = '';
        if (item.status === 'In Stock') statusClass = 'badge-green';
        else if (item.status === 'Low Stock') statusClass = 'badge-red';
        else if (item.status === 'Out of Stock') statusClass = 'badge-red';
        else statusClass = 'badge-yellow';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.stock}</td>
            <td>₱${item.price.toLocaleString()}</td>
            <td><span class="badge ${statusClass}">${item.status}</span></td>
        `;
        tbody.appendChild(tr);
    });
}

function updateCompanyProfileDisplay() {
    const address = document.getElementById('display-address');
    const phone = document.getElementById('display-phone');
    const email = document.getElementById('display-email');
    const website = document.getElementById('display-website');
    const regional = document.getElementById('display-regional');
    const mission = document.getElementById('display-mission');
    const history = document.getElementById('display-history');

    if (address) address.textContent = companyProfile.address;
    if (phone) phone.textContent = companyProfile.phone;
    if (email) email.textContent = companyProfile.email;
    if (website) website.textContent = companyProfile.website;
    if (regional) regional.textContent = companyProfile.regional;
    if (mission) mission.textContent = companyProfile.mission;
    if (history) history.innerHTML = companyProfile.history;
}

function setupNotificationBell() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', openNotificationsModal);
    }
}

function openNotificationsModal() {
    const modal = document.getElementById('notificationsModal');
    const notificationsBody = document.getElementById('notificationsBody');
    if (!modal || !notificationsBody) return;

    notificationsBody.innerHTML = '';
    notificationsData.forEach((notification) => {
        const notifElement = document.createElement('div');
        notifElement.className = `notification-item ${notification.unread ? 'unread' : ''}`;
        notifElement.innerHTML = `
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
            </div>
            <i class="fas fa-bell notification-icon"></i>
        `;
        notificationsBody.appendChild(notifElement);
    });

    modal.style.display = 'block';
}

function closeNotificationsModal() {
    const modal = document.getElementById('notificationsModal');
    if (modal) modal.style.display = 'none';
}

function openMessageModal(messageId) {
    const modal = document.getElementById('messageModal');
    const modalTitle = document.getElementById('messageModalTitle');
    const modalBody = document.getElementById('messageModalBody');

    const message = messageData[messageId];
    if (!message || !modal || !modalTitle || !modalBody) return;

    modalTitle.textContent = message.subject;
    modalBody.innerHTML = `
        <div class="message-modal-content">
            <div class="message-modal-header-info">
                <div><strong>From:</strong> ${message.sender}</div>
                <div><strong>Date:</strong> ${message.date}</div>
            </div>
            <div class="message-modal-body-content">
                <p>${message.content}</p>
            </div>
        </div>
    `;
    modal.style.display = 'block';
}

function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    if (modal) modal.style.display = 'none';
}

let currentReplyMessageId = null;

function openReplyModal(messageId) {
    currentReplyMessageId = messageId;
    const modal = document.getElementById('replyModal');
    const label = document.getElementById('replyToLabel');
    const textarea = document.getElementById('replyModalText');

    const message = messageData[messageId];
    if (!message || !modal || !label || !textarea) return;

    label.textContent = `Reply to: ${message.subject}`;
    textarea.value = '';
    modal.style.display = 'block';
}

function closeReplyModal() {
    const modal = document.getElementById('replyModal');
    const textarea = document.getElementById('replyModalText');
    if (modal) modal.style.display = 'none';
    if (textarea) textarea.value = '';
    currentReplyMessageId = null;
}

function sendReplyFromModal() {
    const textarea = document.getElementById('replyModalText');
    if (!textarea) return;

    const replyText = textarea.value.trim();
    if (replyText === '') {
        alert('Please enter a reply message before sending.');
        return;
    }

    const message = messageData[currentReplyMessageId];
    const subject = message ? message.subject : 'this message';

    if (confirm(`Send reply to "${subject}"?`)) {
        alert('Reply sent successfully!');
        closeReplyModal();
    }
}

function sendMessage(event) {
    event.preventDefault();
    const replyText = document.getElementById('replyMessage');
    if (!replyText) return false;

    const content = replyText.value.trim();
    if (content === '') {
        alert('Please enter a message before sending.');
        return false;
    }

    alert('Message sent successfully!');
    replyText.value = '';
    return false;
}

function clearReply() {
    const replyText = document.getElementById('replyMessage');
    if (replyText) replyText.value = '';
}

function openEditModal(section) {
    const modal = document.getElementById('editModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalTitle || !modalBody) return;

    let content = '';

    switch (section) {
        case 'contact':
            modalTitle.textContent = 'Edit Contact Information';
            content = `
                <div class="input-group">
                    <label>Head Office Address</label>
                    <input type="text" id="edit-address" value="${companyProfile.address}">
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" id="edit-phone" value="${companyProfile.phone}">
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" id="edit-email" value="${companyProfile.email}">
                </div>
                <div class="input-group">
                    <label>Website</label>
                    <input type="url" id="edit-website" value="${companyProfile.website}">
                </div>
                <div class="input-group">
                    <label>Regional Offices</label>
                    <textarea id="edit-regional" rows="4">${companyProfile.regional}</textarea>
                </div>
            `;
            break;

        case 'mission':
            modalTitle.textContent = 'Edit Mission Statement';
            content = `
                <div class="input-group">
                    <label>Mission Statement</label>
                    <textarea id="edit-mission" rows="6">${companyProfile.mission}</textarea>
                </div>
            `;
            break;

        case 'history':
            modalTitle.textContent = 'Edit Company History';
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = companyProfile.history;
            const historyText = Array.from(tempDiv.querySelectorAll('p')).map(p => p.textContent).join('\n\n');
            content = `
                <div class="input-group">
                    <label>Company History</label>
                    <textarea id="edit-history" rows="10">${historyText}</textarea>
                </div>
            `;
            break;

        case 'inventory':
            modalTitle.textContent = 'Edit Inventory';
            content = `
                <div class="input-group">
                    <label>Manage Inventory Items</label>
                    <p style="color: #64748b; font-size: 13px; margin-bottom: 15px;">Edit quantities, prices, and status for each item.</p>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;">Item</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;">Stock</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;">Price (₱)</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryEditBody"></tbody>
                        </table>
                    </div>
                </div>
            `;
            break;
    }

    modalBody.innerHTML = content;
    if (section === 'inventory') {
        const tbody = document.getElementById('inventoryEditBody');
        if (tbody) {
            tbody.innerHTML = '';
            inventoryData.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <input type="text" value="${item.name}" id="inv-name-${index}" style="width: 100%; padding: 6px; border: 2px solid #e2e8f0; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <input type="text" value="${item.stock}" id="inv-stock-${index}" style="width: 100%; padding: 6px; border: 2px solid #e2e8f0; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <input type="number" value="${item.price}" id="inv-price-${index}" style="width: 100%; padding: 6px; border: 2px solid #e2e8f0; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <select id="inv-status-${index}" style="width: 100%; padding: 6px; border: 2px solid #e2e8f0; border-radius: 4px;">
                            <option ${item.status === 'In Stock' ? 'selected' : ''}>In Stock</option>
                            <option ${item.status === 'Limited Stock' ? 'selected' : ''}>Limited Stock</option>
                            <option ${item.status === 'Low Stock' ? 'selected' : ''}>Low Stock</option>
                            <option ${item.status === 'Out of Stock' ? 'selected' : ''}>Out of Stock</option>
                            <option ${item.status === 'Restocking Soon' ? 'selected' : ''}>Restocking Soon</option>
                        </select>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    modal.style.display = 'block';
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.style.display = 'none';
}

function saveEdit() {
    const modalTitleEl = document.getElementById('modalTitle');
    if (!modalTitleEl) return;
    const title = modalTitleEl.textContent;

    if (title === 'Edit Inventory') {
        inventoryData.forEach((item, index) => {
            const nameInput = document.getElementById(`inv-name-${index}`);
            const stockInput = document.getElementById(`inv-stock-${index}`);
            const priceInput = document.getElementById(`inv-price-${index}`);
            const statusInput = document.getElementById(`inv-status-${index}`);

            if (nameInput) item.name = nameInput.value;
            if (stockInput) item.stock = stockInput.value;
            if (priceInput) item.price = parseFloat(priceInput.value);
            if (statusInput) item.status = statusInput.value;
        });

        populateInventoryTable();
        alert('Inventory updated successfully!');
    } else if (title === 'Edit Contact Information') {
        const addressInput = document.getElementById('edit-address');
        const phoneInput = document.getElementById('edit-phone');
        const emailInput = document.getElementById('edit-email');
        const websiteInput = document.getElementById('edit-website');
        const regionalInput = document.getElementById('edit-regional');

        if (addressInput) companyProfile.address = addressInput.value;
        if (phoneInput) companyProfile.phone = phoneInput.value;
        if (emailInput) companyProfile.email = emailInput.value;
        if (websiteInput) companyProfile.website = websiteInput.value;
        if (regionalInput) companyProfile.regional = regionalInput.value;

        updateCompanyProfileDisplay();
        alert('Contact information updated successfully!');
    } else if (title === 'Edit Mission Statement') {
        const missionInput = document.getElementById('edit-mission');
        if (missionInput) {
            companyProfile.mission = missionInput.value;
            updateCompanyProfileDisplay();
        }
        alert('Mission statement updated successfully!');
    } else if (title === 'Edit Company History') {
        const historyInput = document.getElementById('edit-history');
        if (historyInput) {
            const paragraphs = historyInput.value.split('\n\n').filter(p => p.trim() !== '');
            companyProfile.history = paragraphs.map(p => `<p>${p}</p>`).join('\n');
            updateCompanyProfileDisplay();
        }
        alert('Company history updated successfully!');
    } else {
        alert('Changes saved successfully!');
    }

    closeEditModal();
}

window.addEventListener('click', function (event) {
    const messageModal = document.getElementById('messageModal');
    const replyModal = document.getElementById('replyModal');
    const editModal = document.getElementById('editModal');
    const notificationsModal = document.getElementById('notificationsModal');

    if (event.target === messageModal) closeMessageModal();
    if (event.target === replyModal) closeReplyModal();
    if (event.target === editModal) closeEditModal();
    if (event.target === notificationsModal) closeNotificationsModal();
});

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const dateFilter = document.getElementById('dateFilter'); 

  function normalizeDate(dateStr) {
    const txt = (dateStr || '').trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(txt)) return txt; 
    const parts = txt.split('/');
    if (parts.length === 3) {
      const [m, d, y] = parts;
      return `${y}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
    }
    return txt;
  }

  function filterTables() {
    const searchValue = (searchInput?.value || '').toLowerCase();
    const categoryValue = (categoryFilter?.value || '').toLowerCase();
    const dateValue = (dateFilter?.value || ''); 

    const tableBodies = document.querySelectorAll('.request-table tbody');

    tableBodies.forEach(tbody => {
      Array.from(tbody.rows).forEach(row => {
        const c = row.cells;
        if (!c || c.length < 6) {
          row.style.display = '';
          return;
        }

        const batchId = (c[1]?.textContent || '').toLowerCase();
        const itemName = (c[2]?.textContent || '').toLowerCase();
        const category = (c[3]?.textContent || '').toLowerCase();
        const quantity = (c[4]?.textContent || '').toLowerCase();
        const requestDateText = (c[5]?.textContent || '').trim();

        const matchesSearch =
          batchId.includes(searchValue) ||
          itemName.includes(searchValue) ||
          quantity.includes(searchValue);

        const matchesCategory =
          categoryValue === '' || category.includes(categoryValue);

        const normalized = normalizeDate(requestDateText);
        const matchesDate =
          dateValue === '' || normalized === dateValue;

        row.style.display = (matchesSearch && matchesCategory && matchesDate) ? '' : 'none';
      });
    });
  }

  function clearFilters() {
    if (searchInput) searchInput.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (dateFilter) dateFilter.value = '';
    filterTables();
  }

  if (searchInput) searchInput.addEventListener('input', filterTables);
  if (categoryFilter) categoryFilter.addEventListener('change', filterTables);
  if (dateFilter) dateFilter.addEventListener('change', filterTables);

  window.clearFilters = clearFilters;

  filterTables();
});

const logoutBtn = document.querySelector('.logout-button');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    });
}
