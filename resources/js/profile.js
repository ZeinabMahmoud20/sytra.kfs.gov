const sidebar = document.getElementById('sidebar');
const openSidebar = document.getElementById('open-sidebar');
const closeSidebar = document.getElementById('close-sidebar');
const overlay = document.getElementById('overlay');

function toggleSidebar() {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('hidden');
}

openSidebar.addEventListener('click', toggleSidebar);
closeSidebar.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

function openLogoutModal() {
    document.getElementById('logout-modal').classList.remove('hidden');
    document.getElementById('logout-modal').classList.add('flex');
}

function closeLogoutModal() {
    document.getElementById('logout-modal').classList.add('hidden');
    document.getElementById('logout-modal').classList.remove('flex');
}

document.getElementById('logout-modal').addEventListener('click', function (e) {
    if (e.target === this) closeLogoutModal();
});

function confirmLogout() {
    window.location.href = '../auth/login.html';
}


const ddBtn = document.getElementById('user-dropdown-btn');
const ddMenu = document.getElementById('user-dropdown');
if (ddBtn) {
    ddBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        ddMenu.classList.toggle('hidden');
    });
}
document.addEventListener('click', () => {
    if (ddMenu) ddMenu.classList.add('hidden');
});
if (ddMenu) {
    ddMenu.addEventListener('click', e => e.stopPropagation());
}

function loadProfile() {
    const user = JSON.parse(localStorage.getItem('kfs_user')) || {
        name: 'أحمد محمد',
        email: 'ahmed@kfs.gov.eg',
        phone: '01012345678',
        role: 'مدير النظام',
        avatar: 'https://ui-avatars.com/api/?name=Ahmed+Mohamed&background=001f3f&color=fff'
    };
    if (document.getElementById('profile-big-img')) document.getElementById('profile-big-img').src = user.avatar;
    if (document.getElementById('profile-big-name')) document.getElementById('profile-big-name').textContent = user.name;
    if (document.getElementById('profile-big-role')) document.getElementById('profile-big-role').textContent = user.role;
    if (document.getElementById('profile-email')) document.getElementById('profile-email').textContent = user.email;
    if (document.getElementById('profile-phone')) document.getElementById('profile-phone').textContent = user.phone;
}

const sampleReports = [
    { id: 'BLG-001', title: 'بلاغ حريق في منطقة بيلا', status: 'منجز', statusColor: 'green', date: '2026-03-04', icon: 'fa-fire', iconBg: 'bg-red-100 text-red-500' },
    { id: 'BLG-002', title: 'حادث سير على طريق كفر الشيخ', status: 'قيد التنفيذ', statusColor: 'orange', date: '2026-03-05', icon: 'fa-car-crash', iconBg: 'bg-orange-100 text-orange-500' },
    { id: 'BLG-003', title: 'بلاغ فيضان في فوه', status: 'منجز', statusColor: 'green', date: '2026-03-03', icon: 'fa-water', iconBg: 'bg-blue-100 text-blue-500' },
    { id: 'BLG-004', title: 'انهيار جزئي في مبنى سكني', status: 'معلّق', statusColor: 'red', date: '2026-03-02', icon: 'fa-building', iconBg: 'bg-slate-100 text-slate-500' },
];

const statusBadge = {
    green: 'bg-green-100 text-green-700',
    orange: 'bg-orange-100 text-orange-700',
    red: 'bg-red-100 text-red-700'
};

function renderReports() {
    const list = document.getElementById('profile-reports-list');
    if (!list) return;
    list.innerHTML = '';
    sampleReports.forEach(r => {
        list.innerHTML += `
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/50 transition-colors">
            <div class="w-10 h-10 rounded-xl ${r.iconBg} flex items-center justify-center flex-shrink-0">
                <i class="fas ${r.icon}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-800 truncate">${r.title}</p>
                <p class="text-xs text-slate-400 font-medium mt-0.5">${r.id} · ${r.date}</p>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold ${statusBadge[r.statusColor]}">${r.status}</span>
        </div>`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadProfile();
    renderReports();
});

window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
