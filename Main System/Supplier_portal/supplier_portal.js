// Inventory data - connected to Company Profile and Process Order
let inventoryData = [
    { name: 'Face Mask', stock: '500 pcs', price: 500, status: 'In Stock' },
    { name: 'Medical Gloves', stock: '320 boxes', price: 650, status: 'In Stock' },
    { name: 'Syringes', stock: '200 pieces', price: 250, status: 'In Stock' },
    { name: 'Sanitizers', stock: '150 bottles', price: 450, status: 'In Stock' },
    { name: 'Thermometers', stock: '75 units', price: 850, status: 'Low Stock' }
];

// Company profile data
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

// Notifications data
let notificationsData = [
    { id: 1, title: 'Order ORD-001 Received', message: 'New order received from Inventory Manager', time: '2 hours ago', unread: true },
    { id: 2, title: 'Order ORD-002 Accepted', message: 'Order has been successfully accepted', time: '5 hours ago', unread: true },
    { id: 3, title: 'Payment Confirmed - ORD-003', message: 'Payment for order ORD-003 has been processed', time: '1 day ago', unread: true },
    { id: 4, title: 'New Message from Hospital Admin', message: 'Stock availability check request', time: '2 days ago', unread: false },
    { id: 5, title: 'Delivery Scheduled', message: 'Order ORD-004 scheduled for delivery', time: '3 days ago', unread: false }
];

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    populateInventoryTable();
    updateCompanyProfileDisplay();
    setupNotificationBell();
});

// Setup notification bell click event
function setupNotificationBell() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', openNotificationsModal);
    }
}

// Tab switching functionality
document.querySelectorAll('.tab-link').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        switchTab(tab);
    });
});

function switchTab(tab) {
    // Remove active class from all tabs and sections
    document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    
    // Add active class to clicked tab
    const targetBtn = document.querySelector(`[data-tab="${tab}"]`);
    if (targetBtn) {
        targetBtn.classList.add('active');
    }
    
    // Show corresponding section
    const sectionId = tab + '-section';
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
    }
}

// Populate Inventory Table in Company Profile
function populateInventoryTable() {
    const tbody = document.getElementById('inventoryTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    inventoryData.forEach((item, index) => {
        const tr = document.createElement('tr');
        
        let statusClass = '';
        if (item.status === 'In Stock') statusClass = 'badge-green';
        else if (item.status === 'Limited Stock') statusClass = 'badge-yellow';
        else if (item.status === 'Low Stock') statusClass = 'badge-red';
        else if (item.status === 'Out of Stock') statusClass = 'badge-red';
        else statusClass = 'badge-blue';
        
        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.stock}</td>
            <td>₱${item.price.toLocaleString()}</td>
            <td><span class="badge ${statusClass}">${item.status}</span></td>
        `;
        
        tbody.appendChild(tr);
    });
}

// Update company profile display
function updateCompanyProfileDisplay() {
    document.getElementById('display-address').textContent = companyProfile.address;
    document.getElementById('display-phone').textContent = companyProfile.phone;
    document.getElementById('display-email').textContent = companyProfile.email;
    document.getElementById('display-website').textContent = companyProfile.website;
    document.getElementById('display-regional').textContent = companyProfile.regional;
    document.getElementById('display-mission').textContent = companyProfile.mission;
    document.getElementById('display-history').innerHTML = companyProfile.history;
}

// Accept order function
function acceptOrder() {
    if (confirm('Accept this order?')) {
        alert('Order accepted successfully!');
    }
}

// Deny order function
function denyOrder() {
    if (confirm('Are you sure you want to deny this order?')) {
        alert('Order denied.');
    }
}

// Message modal data
const messageData = {
    1: {
        subject: 'Order Inquiry - ORD-001',
        sender: 'Inventory Manager',
        date: '04/10/2025',
        content: 'Hello, I would like to inquire about the availability of Face Masks and Medical Gloves for order ORD-001. Can you confirm the stock levels and estimated delivery time? We need these items urgently for our hospital operations. Please provide details on pricing and any bulk discounts available.'
    },
    2: {
        subject: 'Order Update - ORD-002',
        sender: 'Procurement Team',
        date: '04/08/2025',
        content: 'We need to update the delivery date for order ORD-002. Please confirm if you can accommodate the new schedule. The items are urgently needed for our upcoming medical mission. We would appreciate your prompt response regarding the feasibility of the new delivery timeline.'
    },
    3: {
        subject: 'Stock Availability Check',
        sender: 'Hospital Admin',
        date: '04/07/2025',
        content: 'Good day! We are planning to place a large order next week. Could you please provide current stock levels for Syringes, Sanitizers, and Thermometers? We are also interested in knowing your lead times and minimum order quantities for these items.'
    },
    4: {
        subject: 'Payment Confirmation - ORD-003',
        sender: 'Finance Department',
        date: '04/05/2025',
        content: 'Payment for order ORD-003 has been processed. Please confirm receipt and proceed with shipping arrangements. Thank you for your prompt service. We look forward to receiving the items on the scheduled delivery date.'
    },
    5: {
        subject: 'Delivery Schedule Change',
        sender: 'Logistics Coordinator',
        date: '04/03/2025',
        content: 'Due to unforeseen circumstances, we need to reschedule the delivery for next week\'s orders. Please let us know your availability for the new dates. We understand this may cause inconvenience and appreciate your flexibility in accommodating our request.'
    },
    6: {
        subject: 'New Product Inquiry',
        sender: 'Medical Supplies Buyer',
        date: '04/01/2025',
        content: 'We are interested in adding new medical supplies to our inventory. Do you have catalogs or product lists we can review? We\'re particularly interested in protective equipment, diagnostic tools, and emergency medical supplies. Please send us your latest product catalog.'
    }
};

// Notifications modal functionality
function openNotificationsModal() {
    const modal = document.getElementById('notificationsModal');
    const notificationsBody = document.getElementById('notificationsBody');
    
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
    modal.style.display = 'none';
}

// Message modal functionality
function openMessageModal(messageId) {
    const modal = document.getElementById('messageModal');
    const modalTitle = document.getElementById('messageModalTitle');
    const modalBody = document.getElementById('messageModalBody');
    
    const message = messageData[messageId];
    if (!message) return;
    
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
    modal.style.display = 'none';
}

// Reply modal functionality
let currentReplyMessageId = null;

function openReplyModal(messageId) {
    currentReplyMessageId = messageId;
    const modal = document.getElementById('replyModal');
    const message = messageData[messageId];
    
    if (!message) return;
    
    document.getElementById('replyToLabel').textContent = `Reply to: ${message.subject}`;
    document.getElementById('replyModalText').value = '';
    
    modal.style.display = 'block';
}

function closeReplyModal() {
    const modal = document.getElementById('replyModal');
    modal.style.display = 'none';
    document.getElementById('replyModalText').value = '';
    currentReplyMessageId = null;
}

function sendReplyFromModal() {
    const replyText = document.getElementById('replyModalText').value.trim();
    
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

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const messageModal = document.getElementById('messageModal');
    const replyModal = document.getElementById('replyModal');
    const editModal = document.getElementById('editModal');
    const notificationsModal = document.getElementById('notificationsModal');
    
    if (event.target == messageModal) {
        closeMessageModal();
    }
    if (event.target == replyModal) {
        closeReplyModal();
    }
    if (event.target == editModal) {
        closeEditModal();
    }
    if (event.target == notificationsModal) {
        closeNotificationsModal();
    }
});

function sendMessage(event) {
    event.preventDefault();
    
    const replyText = document.getElementById('replyMessage').value;
    
    if (replyText.trim() === '') {
        alert('Please enter a message before sending.');
        return false;
    }
    
    alert('Message sent successfully!');
    document.getElementById('replyMessage').value = '';
    
    return false;
}

function clearReply() {
    document.getElementById('replyMessage').value = '';
}

// Edit Modal Functions
function openEditModal(section) {
    const modal = document.getElementById('editModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    let content = '';
    
    switch(section) {
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
            // Extract text content from HTML for editing
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
                            <tbody id="inventoryEditBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            break;
    }
    
    modalBody.innerHTML = content;
    
    // If inventory modal, populate the table
    if (section === 'inventory') {
        const tbody = document.getElementById('inventoryEditBody');
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
    
    modal.style.display = 'block';
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
}

function saveEdit() {
    const modalTitle = document.getElementById('modalTitle').textContent;
    
    // If editing inventory, update the inventoryData array
    if (modalTitle === 'Edit Inventory') {
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
        
        // Refresh inventory table
        populateInventoryTable();
        
        alert('Inventory updated successfully!');
    } 
    // If editing contact info
    else if (modalTitle === 'Edit Contact Information') {
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
        
        // Update display
        updateCompanyProfileDisplay();
            
        alert('Contact information updated successfully!');
    }
    // If editing mission
    else if (modalTitle === 'Edit Mission Statement') {
        const missionInput = document.getElementById('edit-mission');
        if (missionInput) {
            companyProfile.mission = missionInput.value;
            updateCompanyProfileDisplay();
        }
        alert('Mission statement updated successfully!');
    }
    // If editing history
    else if (modalTitle === 'Edit Company History') {
        const historyInput = document.getElementById('edit-history');
        if (historyInput) {
            // Convert line breaks to paragraphs
            const paragraphs = historyInput.value.split('\n\n').filter(p => p.trim() !== '');
            companyProfile.history = paragraphs.map(p => `<p>${p}</p>`).join('\n        ');
            updateCompanyProfileDisplay();
        }
        alert('Company history updated successfully!');
    }
    else {
        alert('Changes saved successfully!');
    }
    
    closeEditModal();
}

// Logout functionality
document.querySelector('.logout-button').addEventListener('click', () => {
    if (confirm('Are you sure you want to logout?')) {
        alert('Logging out...');
        // In production, redirect to login page
        // window.location.href = '/login';
    }
});