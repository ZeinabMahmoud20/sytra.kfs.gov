// ======= Sidebar Toggle =======
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

// ======= User Dropdown =======
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

// ======= Logout Modal =======
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

// ======= User Data (simulated localStorage) =======
let userData = JSON.parse(localStorage.getItem('kfs_user')) || {
    name: 'أحمد محمد',
    email: 'ahmed@kfs.gov.eg',
    phone: '01012345678',
    role: 'مدير النظام',
    roleValue: 'admin',
    avatar: DEFAULT_AVATAR
};


// Map role values to Arabic labels
const roleLabels = {
    admin: 'مدير النظام',
    moderator: 'مشغل بلاغات',
    viewer: 'مراقب'
};

// ======= Apply saved data to page on load =======
function applyUserData() {
    const nameInput = document.querySelector('input[type="text"]');
    const emailInput = document.querySelector('input[type="email"]');
    const phoneInput = document.querySelector('input[type="tel"]');
    const roleSelect = document.querySelector('select');

    if (nameInput) nameInput.value = userData.name;
    if (emailInput) emailInput.value = userData.email;
    if (phoneInput) phoneInput.value = userData.phone;
    if (roleSelect) roleSelect.value = userData.roleValue;

    // Update display elements
    const profileName = document.getElementById('profile-display-name');
    const profileRole = document.getElementById('profile-display-role');
    const hAvatar = document.getElementById('header-avatar');
    const hName = document.getElementById('header-name');
    const hRole = document.getElementById('header-role');
    const pPreview = document.getElementById('profile-preview');

    if (profileName) profileName.textContent = userData.name;
    if (profileRole) profileRole.innerHTML = `<i class="fas fa-user-shield"></i> ${userData.role}`;
    if (hAvatar) hAvatar.src = userData.avatar;
    if (hName) hName.textContent = userData.name;
    if (hRole) hRole.textContent = userData.role;
    if (pPreview) pPreview.src = userData.avatar;
}

// ======= Edit Mode =======
const editBtn = document.getElementById('editBtn');
const saveBtn = document.getElementById('saveBtn');
const avatarUpload = document.getElementById('avatar-upload');
const avatarUploadLabel = document.getElementById('avatar-upload-label');
const deleteAvatarBtn = document.getElementById('delete-avatar-btn');

function setupEvents() {
    const inputsAndSelects = document.querySelectorAll('.form-fields input:not([type="password"]):not([type="checkbox"]), .form-fields select');
    const toggleCheckboxes = document.querySelectorAll('.toggle-checkbox');
    const toggleLabels = document.querySelectorAll('.toggle-label');

    // Avatar upload preview
    if (avatarUpload) {
        avatarUpload.addEventListener('change', function (event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const pPreview = document.getElementById('profile-preview');
                    if (pPreview) pPreview.src = e.target.result;
                    userData.avatar = e.target.result;
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    }

    // Delete avatar button
    if (deleteAvatarBtn) {
        deleteAvatarBtn.addEventListener('click', function () {
            const nameInput = document.querySelector('input[type="text"]');
            const defaultUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent((nameInput ? nameInput.value : "") || userData.name)}&background=001f3f&color=fff`;
            const pPreview = document.getElementById('profile-preview');
            if (pPreview) pPreview.src = defaultUrl;
            userData.avatar = DEFAULT_AVATAR;
            if (avatarUpload) avatarUpload.value = '';
        });
    }

    if (editBtn) {
        editBtn.addEventListener('click', () => {
            // Enable text/email/tel/select fields
            inputsAndSelects.forEach(el => {
                el.removeAttribute('disabled');
                el.classList.remove('bg-slate-50');
                el.classList.add('bg-white', 'ring-2', 'ring-accent/10');
            });
            // Enable password fields
            document.querySelectorAll('input[type="password"]').forEach(el => {
                el.removeAttribute('disabled');
                el.classList.remove('bg-slate-50');
                el.classList.add('bg-white');
            });
            // Enable avatar upload
            if (avatarUpload) avatarUpload.removeAttribute('disabled');
            if (avatarUploadLabel) {
                avatarUploadLabel.style.pointerEvents = 'auto';
                avatarUploadLabel.style.opacity = '1';
            }
            // Show delete avatar button
            if (deleteAvatarBtn) {
                deleteAvatarBtn.classList.remove('hidden');
                deleteAvatarBtn.classList.add('flex');
            }
            // Enable toggles
            toggleCheckboxes.forEach(el => {
                el.removeAttribute('disabled');
                el.classList.remove('cursor-not-allowed');
                el.classList.add('cursor-pointer');
            });
            toggleLabels.forEach(el => {
                el.classList.remove('cursor-not-allowed');
                el.classList.add('cursor-pointer');
            });
            // Swap buttons
            editBtn.classList.add('hidden');
            if (saveBtn) saveBtn.classList.remove('hidden', 'opacity-50', 'cursor-not-allowed');
        });
    }

    // ======= Save Logic =======
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            if (passwordInputs.length >= 3 && (passwordInputs[1].value || passwordInputs[2].value)) {
                if (!passwordInputs[0].value) {
                    showToast('يرجى إدخال كلمة المرور الحالية أولاً.', 'error'); return;
                }
                if (passwordInputs[1].value !== passwordInputs[2].value) {
                    showToast('كلمتا المرور غير متطابقتين!', 'error'); return;
                }
            }

            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
            saveBtn.disabled = true;

            setTimeout(() => {
                // Read new values
                const nameInp = document.querySelector('input[type="text"]');
                const emailInp = document.querySelector('input[type="email"]');
                const phoneInp = document.querySelector('input[type="tel"]');
                const roleSel = document.querySelector('select');

                const newName = (nameInp ? nameInp.value.trim() : "") || userData.name;
                const newEmail = (emailInp ? emailInp.value.trim() : "") || userData.email;
                const newPhone = (phoneInp ? phoneInp.value.trim() : "") || userData.phone;
                const newRoleValue = roleSel ? roleSel.value : userData.roleValue;
                const newRole = roleLabels[newRoleValue] || userData.role;

                // Update userData object
                userData.name = newName;
                userData.email = newEmail;
                userData.phone = newPhone;
                userData.role = newRole;
                userData.roleValue = newRoleValue;

                // Save to localStorage
                localStorage.setItem('kfs_user', JSON.stringify(userData));

                // Update all display elements live
                applyUserData();

                // Clear passwords
                passwordInputs.forEach(input => input.value = '');

                // Lock inputs back
                inputsAndSelects.forEach(el => {
                    el.setAttribute('disabled', 'true');
                    el.classList.remove('bg-white', 'ring-2', 'ring-accent/10');
                    el.classList.add('bg-slate-50');
                });
                document.querySelectorAll('input[type="password"]').forEach(el => {
                    el.setAttribute('disabled', 'true');
                    el.classList.remove('bg-white');
                    el.classList.add('bg-slate-50');
                });
                // Lock avatar
                if (avatarUpload) avatarUpload.setAttribute('disabled', 'true');
                if (avatarUploadLabel) {
                    avatarUploadLabel.style.pointerEvents = 'none';
                    avatarUploadLabel.style.opacity = '0.5';
                }
                // Hide delete avatar btn
                if (deleteAvatarBtn) {
                    deleteAvatarBtn.classList.add('hidden');
                    deleteAvatarBtn.classList.remove('flex');
                }
                // Lock toggles
                toggleCheckboxes.forEach(el => {
                    el.setAttribute('disabled', 'true');
                    el.classList.add('cursor-not-allowed');
                    el.classList.remove('cursor-pointer');
                });
                toggleLabels.forEach(el => {
                    el.classList.add('cursor-not-allowed');
                    el.classList.remove('cursor-pointer');
                });
                // Restore save btn
                saveBtn.innerHTML = '<i class="fas fa-save"></i> حفظ التغييرات';
                saveBtn.disabled = false;
                saveBtn.classList.add('hidden');
                if (editBtn) editBtn.classList.remove('hidden');

                showToast('تم حفظ البيانات بنجاح! ✓', 'success');
            }, 900);
        });
    }

    const deleteConfirmInput = document.getElementById('delete-confirm-input');
    if (deleteConfirmInput) {
        deleteConfirmInput.addEventListener('input', function () {
            const confirmBtn = document.getElementById('confirm-delete-btn');
            const valid = this.value.trim() === 'احذف حسابي';
            if (confirmBtn) {
                confirmBtn.disabled = !valid;
                confirmBtn.classList.toggle('opacity-50', !valid);
                confirmBtn.classList.toggle('cursor-not-allowed', !valid);
            }
        });
    }
}

// ======= Delete Account Modal =======
function openDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    const input = document.getElementById('delete-confirm-input');
    if (input) input.value = '';
    const btn = document.getElementById('confirm-delete-btn');
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function confirmDeleteAccount() {
    localStorage.removeItem('kfs_user');
    localStorage.clear();
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl p-10 max-w-md w-full mx-4 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">تم حذف الحساب</h3>
                <p class="text-slate-500">تم مسح جميع بياناتك بنجاح. سيتم تحويلك لصفحة تسجيل الدخول خلال ثوانٍ...</p>
            </div>`;
    }
    setTimeout(() => { window.location.href = '../auth/login.html'; }, 2500);
}

// ======= Toast Notifications =======
function showToast(message, type = 'success') {
    const existing = document.getElementById('toast-notif');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'toast-notif';
    const colors = type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
    toast.className = `fixed bottom-8 left-1/2 -translate-x-1/2 ${colors} px-7 py-4 rounded-2xl shadow-2xl flex items-center gap-3 font-bold text-base z-50 transition-all`;
    toast.innerHTML = `<i class="fas ${icon} text-xl"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.style.opacity = '0', 2500);
    setTimeout(() => toast.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    applyUserData();
    setupEvents();
});

window.toggleSidebar = toggleSidebar;
window.openLogoutModal = openLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
window.openDeleteModal = openDeleteModal;
window.closeDeleteModal = closeDeleteModal;
window.confirmDeleteAccount = confirmDeleteAccount;
