/**
 * layout.js
 * سكريبت مشترك لكل صفحات النظام (بعد تسجيل الدخول):
 * - فتح/قفل السايدبار (للموبايل)
 * - دروب داون المستخدم في الهيدر
 * - فتح/قفل مودال تسجيل الخروج
 *
 * ملحوظة: ده بديل ملفات user-sync.js + الأكواد المكررة اللي كانت
 * موجودة في نهاية كل ملف JS خاص بكل صفحة (dashboard.js, add-report.js...)
 * بما إن بيانات المستخدم بقت جاية من السيرفر مباشرة (Blade)، مبقاش محتاجين
 * localStorage خالص.
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const openSidebar = document.getElementById('open-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const overlay = document.getElementById('overlay');

    function toggleSidebar() {
        // بنتحكم في ظهور/اختفاء السايدبار عن طريق كلاس Tailwind (translate-x-full)
        // بدل كلاس CSS مخصص قديم (active) كان معتمد على ملف CSS مانقلناهوش
        if (sidebar) sidebar.classList.toggle('translate-x-full');
        if (overlay) overlay.classList.toggle('hidden');
    }

    if (openSidebar) openSidebar.addEventListener('click', toggleSidebar);
    if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);

    const ddBtn = document.getElementById('user-dropdown-btn');
    const ddMenu = document.getElementById('user-dropdown');
    if (ddBtn) {
        ddBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (ddMenu) ddMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', function () {
            if (ddMenu) ddMenu.classList.add('hidden');
        });
        if (ddMenu) ddMenu.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    const logoutModal = document.getElementById('logout-modal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function (e) {
            if (e.target === this) window.closeLogoutModal();
        });
    }
});

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

window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;