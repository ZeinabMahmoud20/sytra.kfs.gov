const sidebar = document.getElementById('sidebar');
const openSidebar = document.getElementById('open-sidebar');
const closeSidebar = document.getElementById('close-sidebar');
const overlay = document.getElementById('overlay');

function toggleSidebar() {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('hidden');
}

if (openSidebar) openSidebar.addEventListener('click', toggleSidebar);
if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
if (overlay) overlay.addEventListener('click', toggleSidebar);

const ddBtn = document.getElementById('user-dropdown-btn');
const ddMenu = document.getElementById('user-dropdown');
if (ddBtn) {
    ddBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        ddMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', () => ddMenu.classList.add('hidden'));
    ddMenu.addEventListener('click', e => e.stopPropagation());
}

Chart.defaults.font.family = "'Cairo', sans-serif";
Chart.defaults.color = '#64748b';
Chart.defaults.scale.grid.color = '#f1f5f9';

function initCharts() {
    const ctxLocations = document.getElementById('locationsChart')?.getContext('2d');
    if (ctxLocations) {
        new Chart(ctxLocations, {
            type: 'bar',
            data: {
                labels: ['كفر الشيخ', 'دسوق', 'بيلا', 'الحامول', 'بلطيم', 'سيدي سالم', 'الرياض', 'قلين', 'فوة', 'مطوبس', 'البرلس'],
                datasets: [{
                    label: 'عدد البلاغات',
                    data: [240, 180, 120, 150, 90, 110, 60, 85, 70, 95, 40],
                    backgroundColor: '#001f3f',
                    borderRadius: 8,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#001f3f',
                        titleFont: { family: 'Cairo', size: 14 },
                        bodyFont: { family: 'Cairo', size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxTypes = document.getElementById('typesChart')?.getContext('2d');
    if (ctxTypes) {
        new Chart(ctxTypes, {
            type: 'doughnut',
            data: {
                labels: ['حريق', 'حادث طريق', 'مرافق عامة', 'أمنيات', 'أخرى'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#001f3f',
                        titleFont: { family: 'Cairo', size: 14 },
                        bodyFont: { family: 'Cairo', size: 14, weight: 'bold' },
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: (context) => ' ' + context.label + ': ' + context.parsed + '%'
                        }
                    }
                }
            }
        });
    }

    const ctxTimeline = document.getElementById('timelineChart')?.getContext('2d');
    if (ctxTimeline) {
        const gradient = ctxTimeline.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(230, 126, 34, 0.4)');
        gradient.addColorStop(1, 'rgba(230, 126, 34, 0)');

        new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: ['1', '3', '5', '7', '9', '11', '13', '15', '17', '19', '21', '23', '25', '27', '29', '31'],
                datasets: [{
                    label: 'البلاغات اليومية',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 24, 18, 20, 32, 28, 25, 22, 15],
                    borderColor: '#e67e22',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#e67e22',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#001f3f',
                        titleFont: { family: 'Cairo', size: 14 },
                        bodyFont: { family: 'Cairo', size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

const searchBtn = document.getElementById('report-search-btn');
const centerSelect = document.getElementById('report-center');

function applyFilters() {
    const center = centerSelect.value;
    const from = document.getElementById('report-from-date').value;
    const to = document.getElementById('report-to-date').value;

    const note = document.createElement('div');
    note.className = 'fixed bottom-10 left-10 bg-primary text-white px-8 py-4 rounded-2xl shadow-2xl font-black z-[100] flex items-center gap-3';
    note.innerHTML = `<i class="fas fa-sync-alt fa-spin text-accent"></i> جارِ تحديث البيانات لـ ${centerSelect.options[centerSelect.selectedIndex].text}...`;
    document.body.appendChild(note);

    setTimeout(() => {
        if (document.getElementById('stat-total')) document.getElementById('stat-total').innerText = Math.floor(Math.random() * 500) + 300;
        if (document.getElementById('stat-done')) document.getElementById('stat-done').innerText = Math.floor(Math.random() * 400) + 200;
        if (document.getElementById('stat-pending')) document.getElementById('stat-pending').innerText = Math.floor(Math.random() * 50);

        note.innerHTML = `<i class="fas fa-check-circle text-accent"></i> تم تحديث التقارير بنجاح`;
        setTimeout(() => note.remove(), 2000);
    }, 800);
}

if (searchBtn) searchBtn.addEventListener('click', applyFilters);
if (centerSelect) centerSelect.addEventListener('change', applyFilters);

let currentFormat = '';
function confirmExport(format) {
    currentFormat = format;
    const modal = document.getElementById('export-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    const icon = document.getElementById('export-icon');
    if (icon) {
        if (format === 'Excel') icon.className = 'fas fa-file-excel text-emerald-600';
        else icon.className = 'fas fa-file-pdf text-red-600';
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
    note.className = 'fixed bottom-10 left-10 bg-primary text-white px-8 py-4 rounded-2xl shadow-2xl font-black z-[100] flex items-center gap-3';
    note.innerHTML = `<i class="fas fa-download text-accent"></i> تم تصدير الملف بصيغة ${currentFormat} بنجاح`;
    document.body.appendChild(note);
    setTimeout(() => note.remove(), 3000);
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
    logoutModal.addEventListener('click', (e) => {
        if (e.target === logoutModal) closeLogoutModal();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCharts();
});

window.confirmExport = confirmExport;
window.closeExportModal = closeExportModal;
window.executeExport = executeExport;
window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
