const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const openSidebar = document.getElementById('open-sidebar');
const closeSidebar = document.getElementById('close-sidebar');

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

const filterBtn = document.getElementById('filter-dropdown-btn');
const filterMenu = document.getElementById('filter-dropdown');
if (filterBtn) {
    filterBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (filterMenu) filterMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', () => { if (filterMenu) filterMenu.classList.add('hidden'); });
    if (filterMenu) filterMenu.addEventListener('click', e => e.stopPropagation());
}

function closeLogoutModal() {
    const m = document.getElementById('logout-modal');
    if (m) {
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
}

function openLogoutModal() {
    const m = document.getElementById('logout-modal');
    if (m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
}

function confirmLogout() {
    window.location.href = '../auth/login.html';
}

const allTasks = [
    { id: 1, reportNum: '9842', agency: 'الحماية المدنية', agencyColor: 'bg-red-500', directionTime: '10:32 ص', arrivalTime: '10:45 ص', status: 'وصل للموقع', statusClass: 'bg-green-100 text-green-700' },
    { id: 2, reportNum: '9842', agency: 'الإسعاف', agencyColor: 'bg-blue-500', directionTime: '10:32 ص', arrivalTime: '10:42 ص', status: 'وصل للموقع', statusClass: 'bg-green-100 text-green-700' },
    { id: 3, reportNum: '9843', agency: 'شركة الكهرباء', agencyColor: 'bg-orange-500', directionTime: '10:50 ص', arrivalTime: 'قيد التحرك...', status: 'متحرك الآن', statusClass: 'bg-orange-100 text-orange-700' },
    { id: 4, reportNum: '9843', agency: 'شركة المياه', agencyColor: 'bg-blue-700', directionTime: '10:55 ص', arrivalTime: 'في الانتظار', status: 'انتظار التمم', statusClass: 'bg-slate-200 text-slate-600' },
    { id: 5, reportNum: '9844', agency: 'المرور', agencyColor: 'bg-yellow-500', directionTime: '11:05 ص', arrivalTime: '11:15 ص', status: 'وصل للموقع', statusClass: 'bg-green-100 text-green-700' },
    { id: 6, reportNum: '9845', agency: 'النجدة', agencyColor: 'bg-indigo-500', directionTime: '11:10 ص', arrivalTime: '11:20 ص', status: 'وصل للموقع', statusClass: 'bg-green-100 text-green-700' },
    { id: 7, reportNum: '9846', agency: 'الغاز الطبيعي', agencyColor: 'bg-emerald-500', directionTime: '11:15 ص', arrivalTime: 'قيد التحرك...', status: 'متحرك الآن', statusClass: 'bg-orange-100 text-orange-700' },
    { id: 8, reportNum: '9847', agency: 'مديرية الأمن', agencyColor: 'bg-red-600', directionTime: '11:20 ص', arrivalTime: 'في الانتظار', status: 'انتظار التمم', statusClass: 'bg-slate-200 text-slate-600' }
];

let displayTasks = [...allTasks];
let currentPage = 1;
const itemsPerPage = 4;

function renderTasks() {
    const tbody = document.getElementById('tasks-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';

    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageData = displayTasks.slice(start, end);

    pageData.forEach(task => {
        tbody.innerHTML += `
            <tr class="table-row-hover transition-colors animate-fadeIn border-b border-slate-50 last:border-0">
                <td class="px-6 py-5 font-bold text-slate-400">${task.id}</td>
                <td class="px-6 py-5 font-black text-primary">#${task.reportNum}</td>
                <td class="px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full ${task.agencyColor} shadow-sm"></span>
                        <span class="font-bold text-slate-700">${task.agency}</span>
                    </div>
                </td>
                <td class="px-6 py-5 text-slate-500 font-semibold">${task.directionTime}</td>
                <td class="px-6 py-5 text-slate-400 font-semibold">${task.arrivalTime}</td>
                <td class="px-6 py-5 text-center">
                    <span class="px-3.5 py-1.5 ${task.statusClass} rounded-xl text-xs font-black shadow-sm">${task.status}</span>
                </td>
                <td class="px-6 py-5 text-center">
                    <button class="w-9 h-9 bg-slate-100 text-slate-600 rounded-xl hover:bg-accent hover:text-white transition-all shadow-sm">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(displayTasks.length / itemsPerPage);
    const controls = document.getElementById('pagination-controls');
    const info = document.getElementById('pagination-info');

    if (info) {
        const startRange = displayTasks.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const endRange = Math.min(currentPage * itemsPerPage, displayTasks.length);
        info.textContent = `عرض ${startRange} إلى ${endRange} من أصل ${displayTasks.length} تمم`;
    }

    if (controls) {
        controls.innerHTML = '';

        const prev = document.createElement('button');
        prev.className = `w-11 h-11 rounded-2xl border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-all ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : 'shadow-sm active:scale-95'}`;
        prev.innerHTML = '<i class="fas fa-chevron-right text-xs"></i>';
        prev.onclick = () => { if (currentPage > 1) { currentPage--; renderTasks(); } };
        controls.appendChild(prev);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `w-11 h-11 rounded-2xl font-black transition-all ${currentPage === i ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; renderTasks(); };
            controls.appendChild(btn);
        }

        const next = document.createElement('button');
        next.className = `w-11 h-11 rounded-2xl border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-all ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : 'shadow-sm active:scale-95'}`;
        next.innerHTML = '<i class="fas fa-chevron-left text-xs"></i>';
        next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTasks(); } };
        controls.appendChild(next);
    }
}

function applySort(type) {
    if (type === 'newest') displayTasks.sort((a, b) => b.id - a.id);
    else if (type === 'oldest') displayTasks.sort((a, b) => a.id - b.id);
    else if (type === 'id') displayTasks.sort((a, b) => a.reportNum.localeCompare(b.reportNum));

    currentPage = 1;
    renderTasks();
    if (filterMenu) filterMenu.classList.add('hidden');
}

const searchInput = document.getElementById('task-search');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase();
        displayTasks = allTasks.filter(t => t.agency.includes(val) || t.reportNum.includes(val));
        currentPage = 1;
        renderTasks();
    });
}

document.addEventListener('DOMContentLoaded', renderTasks);

window.toggleSidebar = toggleSidebar;
window.closeLogoutModal = closeLogoutModal;
window.openLogoutModal = openLogoutModal;
window.confirmLogout = confirmLogout;
window.applySort = applySort;
