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

const allSignals = [
    { id: 1, ref: 'SIG-2026-001', type: 'توجيه فني', typeIcon: 'fa-comment-dots', typeColor: 'text-orange-600', typeBg: 'bg-orange-100', date: '2026/03/04', time: '11:15 ص', subject: 'توجيه سيارات الحماية المدنية لقطاع بلطيم لتغطية حادث طريق', recipients: [{ n: 'ح', c: 'bg-red-500' }, { n: 'إ', c: 'bg-blue-500' }, { n: 'ن', c: 'bg-slate-800' }] },
    { id: 2, ref: 'SIG-2026-002', type: 'إشارة عاجلة', typeIcon: 'fa-bolt', typeColor: 'text-red-600', typeBg: 'bg-red-100', date: '2026/03/04', time: '09:30 ص', subject: 'تغيير وردية العمل في غرفة العمليات المركزية لشهر مارس القادم ورفع درجة الاستعداد القصوى', recipients: [{ n: 'ك', c: 'bg-indigo-500' }, { n: 'م', c: 'bg-emerald-500' }] },
    { id: 3, ref: 'SIG-2026-003', type: 'تنبيه جوي', typeIcon: 'fa-cloud-showers-heavy', typeColor: 'text-blue-600', typeBg: 'bg-blue-100', date: '2026/03/05', time: '08:00 ص', subject: 'ورود بلاغ من هيئة الأرصاد الجوية بوجود منخفض جوي قادم من البحر المتوسط', recipients: [{ n: 'ح', c: 'bg-red-500' }, { n: 'م', c: 'bg-emerald-500' }, { n: 'ك', c: 'bg-indigo-500' }] },
    { id: 4, ref: 'SIG-2026-004', type: 'إدارية', typeIcon: 'fa-folder-open', typeColor: 'text-slate-600', typeBg: 'bg-slate-100', date: '2026/03/05', time: '01:20 م', subject: 'عقد اجتماع الدوري الأسبوعي لمديري القطاعات بالمحافظة لمناقشة المستجدات', recipients: [{ n: 'أ', c: 'bg-primary' }] },
    { id: 5, ref: 'SIG-2026-005', type: 'أمنية', typeIcon: 'fa-shield-alt', typeColor: 'text-indigo-600', typeBg: 'bg-indigo-100', date: '2026/03/05', time: '04:45 م', subject: 'تشديد الحراسات على المنشآت الحيوية والكباري بالمنطقة الشمالية', recipients: [{ n: 'ن', c: 'bg-slate-800' }, { n: 'ش', c: 'bg-red-600' }] },
    { id: 6, ref: 'SIG-2026-006', type: 'إشارة عاجلة', typeIcon: 'fa-bolt', typeColor: 'text-red-600', typeBg: 'bg-red-100', date: '2026/03/05', time: '11:15 ص', subject: 'توجيه سيارات الحماية المدنية لقطاع بلطيم لتغطية حادث طريق نتيحه تصادم سيارتين نقل', recipients: [{ n: 'ح', c: 'bg-red-500' }, { n: 'إ', c: 'bg-blue-500' }, { n: 'ن', c: 'bg-slate-800' }] },
    { id: 7, ref: 'SIG-2026-007', type: 'توجيه فني', typeIcon: 'fa-comment-dots', typeColor: 'text-orange-600', typeBg: 'bg-orange-100', date: '2026/03/06', time: '10:15 ص', subject: 'إجراء تجربة طوارئ حية في محطة كهرباء مطوبس للتدريب على التعامل مع الحرائق', recipients: [{ n: 'ك', c: 'bg-indigo-500' }, { n: 'ح', c: 'bg-red-500' }] },
    { id: 8, ref: 'SIG-2026-008', type: 'إدارية', typeIcon: 'fa-folder-open', typeColor: 'text-slate-600', typeBg: 'bg-slate-100', date: '2026/03/06', time: '12:00 م', subject: 'تحديث بيانات الموظفين في منظومة الشبكة الوطنية للطوارئ', recipients: [{ n: 'أ', c: 'bg-primary' }, { n: 'م', c: 'bg-emerald-500' }] }
];

let filteredSignals = [...allSignals];
let currentPage = 1;
const itemsPerPage = 5;

function renderSignals() {
    const tbody = document.getElementById('signals-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';

    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageData = filteredSignals.slice(start, end);

    pageData.forEach((sig, index) => {
        const recipientsHtml = sig.recipients.map(r => `
            <div class="w-8 h-8 rounded-full ${r.c} border-2 border-white text-white flex items-center justify-center text-[10px] font-black shadow-sm -ml-2" title="${r.n}">
                ${r.n}
            </div>
        `).join('');

        tbody.innerHTML += `
            <tr class="table-row-hover transition-colors animate-fadeIn" style="animation-delay: ${index * 0.05}s">
                <td class="px-6 py-5 font-bold text-slate-400 text-sm">${sig.id}</td>
                <td class="px-6 py-5">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[10px] font-black text-slate-400 font-mono tracking-tighter">${sig.ref}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg ${sig.typeBg} ${sig.typeColor} flex items-center justify-center text-[10px]">
                                <i class="fas ${sig.typeIcon}"></i>
                            </div>
                            <span class="font-bold text-slate-700 text-sm">${sig.type}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-5">
                    <p class="font-black text-slate-700 text-xs">${sig.date}</p>
                    <p class="text-[10px] text-slate-400 font-bold">${sig.time}</p>
                </td>
                <td class="px-6 py-5">
                    <p class="text-slate-600 font-bold text-sm leading-relaxed max-w-sm line-clamp-1 hover:line-clamp-none transition-all cursor-pointer" title="${sig.subject}">
                        ${sig.subject}
                    </p>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center pl-2">
                        ${recipientsHtml}
                    </div>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center justify-center gap-2">
                        <button class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center justify-center"><i class="fas fa-eye text-xs"></i></button>
                        <button class="w-9 h-9 bg-slate-100 text-slate-600 rounded-xl hover:bg-primary hover:text-white transition-all shadow-sm flex items-center justify-center"><i class="fas fa-print text-xs"></i></button>
                        <button class="w-9 h-9 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm flex items-center justify-center"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });

    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredSignals.length / itemsPerPage);
    const controls = document.getElementById('pagination-controls');
    const info = document.getElementById('pagination-info');

    if (info) {
        const startRange = filteredSignals.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const endRange = Math.min(currentPage * itemsPerPage, filteredSignals.length);
        info.textContent = `عرض ${startRange} إلى ${endRange} من أصل ${filteredSignals.length} إشارة`;
    }

    if (controls) {
        controls.innerHTML = '';

        const prevBtn = document.createElement('button');
        prevBtn.className = `w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-all ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : 'shadow-sm'}`;
        prevBtn.innerHTML = '<i class="fas fa-chevron-right text-[10px]"></i>';
        prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderSignals(); } };
        controls.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `w-10 h-10 rounded-xl font-black text-sm transition-all ${currentPage === i ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'border border-slate-200 text-slate-500 hover:bg-slate-50'}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; renderSignals(); };
            controls.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.className = `w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-all ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : 'shadow-sm'}`;
        nextBtn.innerHTML = '<i class="fas fa-chevron-left text-[10px]"></i>';
        nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderSignals(); } };
        controls.appendChild(nextBtn);
    }
}

const searchInput = document.getElementById('signal-search');
const fromDateBtn = document.getElementById('date-search-btn');
const fromDateInput = document.getElementById('from-date');
const toDateInput = document.getElementById('to-date');

function applyFilters() {
    const textVal = searchInput ? searchInput.value.toLowerCase() : '';
    const fromDate = fromDateInput ? fromDateInput.value : '';
    const toDate = toDateInput ? toDateInput.value : '';

    filteredSignals = allSignals.filter(s => {
        const textMatch = s.subject.toLowerCase().includes(textVal) ||
            s.ref.toLowerCase().includes(textVal) ||
            s.type.toLowerCase().includes(textVal);

        const sigDate = s.date.replace(/\//g, '-');
        let dateMatch = true;
        if (fromDate) dateMatch = dateMatch && (sigDate >= fromDate);
        if (toDate) dateMatch = dateMatch && (sigDate <= toDate);

        return textMatch && dateMatch;
    });

    currentPage = 1;
    renderSignals();
}

if (searchInput) searchInput.addEventListener('input', applyFilters);
if (fromDateBtn) fromDateBtn.addEventListener('click', applyFilters);

let currentExportFormat = '';
function confirmExport(format) {
    currentExportFormat = format;
    const formatElem = document.getElementById('export-format');
    if (formatElem) formatElem.textContent = format;

    const container = document.getElementById('export-icon-container');
    const icon = document.getElementById('export-icon');

    if (container && icon) {
        if (format === 'Excel') {
            container.className = 'w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg bg-emerald-100 text-emerald-600';
            icon.className = 'fas fa-file-excel';
        } else {
            container.className = 'w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg bg-red-100 text-red-600';
            icon.className = 'fas fa-file-pdf';
        }
    }

    const modal = document.getElementById('export-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeExportModal() {
    const modal = document.getElementById('export-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function executeExport() {
    closeExportModal();
    const note = document.createElement('div');
    note.className = 'fixed bottom-10 left-10 bg-primary text-white px-8 py-4 rounded-2xl shadow-2xl font-black z-[100] animate-fadeIn flex items-center gap-3';
    note.innerHTML = `<i class="fas fa-check-circle text-accent"></i> تم بدء تحميل ملف ${currentExportFormat} بنجاح`;
    document.body.appendChild(note);
    setTimeout(() => note.remove(), 4000);
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

document.addEventListener('DOMContentLoaded', renderSignals);

window.toggleSidebar = toggleSidebar;
window.confirmExport = confirmExport;
window.closeExportModal = closeExportModal;
window.executeExport = executeExport;
window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
