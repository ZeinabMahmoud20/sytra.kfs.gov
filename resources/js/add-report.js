const sidebar = document.getElementById('sidebar');
const openSidebar = document.getElementById('open-sidebar');
const closeSidebar = document.getElementById('close-sidebar');
const overlay = document.getElementById('overlay');

function toggleSidebar() {
    if (sidebar) sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('hidden');
}

if (openSidebar) openSidebar.addEventListener('click', toggleSidebar);
if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
if (overlay) overlay.addEventListener('click', toggleSidebar);

const ddBtn = document.getElementById('user-dropdown-btn');
const ddMenu = document.getElementById('user-dropdown');
if (ddBtn) {
    ddBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (ddMenu) ddMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', () => { if (ddMenu) ddMenu.classList.add('hidden'); });
    if (ddMenu) ddMenu.addEventListener('click', e => e.stopPropagation());
}

const reportForm = document.getElementById('addReportForm');
if (reportForm) {
    reportForm.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('تم تسجيل البلاغ بنجاح! جاري تحويلك للوحة التحكم...');
        window.location.href = 'dashboard.html';
    });
}

function openLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function confirmLogout() {
    window.location.href = '../auth/login.html';
}

const logoutModal = document.getElementById('logout-modal');
if (logoutModal) {
    logoutModal.addEventListener('click', function (e) {
        if (e.target === this) closeLogoutModal();
    });
}

window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
