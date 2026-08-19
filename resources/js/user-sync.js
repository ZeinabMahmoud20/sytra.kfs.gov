/**
 * user-sync.js
 * Shared script — loads user data from localStorage and updates
 * the header profile area on every page that includes this file.
 */

const DEFAULT_AVATAR = 'https://ui-avatars.com/api/?name=Ahmed+Mohamed&background=001f3f&color=fff';

function getUser() {
    return JSON.parse(localStorage.getItem('kfs_user')) || {
        name: 'أحمد محمد',
        email: 'ahmed@kfs.gov.eg',
        phone: '01012345678',
        role: 'مدير النظام',
        roleValue: 'admin',
        avatar: DEFAULT_AVATAR
    };
}

function applyUserToHeader() {
    const user = getUser();

    const avatar = document.getElementById('header-avatar');
    const name = document.getElementById('header-name');
    const role = document.getElementById('header-role');
    const ddName = document.getElementById('dd-user-name');
    const ddRole = document.getElementById('dd-user-role');
    const ddImg = document.getElementById('dd-user-img');

    if (avatar) avatar.src = user.avatar;
    if (name) name.textContent = user.name;
    if (role) role.textContent = user.role;
    if (ddName) ddName.textContent = user.name;
    if (ddRole) ddRole.textContent = user.role;
    if (ddImg) ddImg.src = user.avatar;
}

// Run on DOM ready
document.addEventListener('DOMContentLoaded', applyUserToHeader);

// Also listen for storage changes (cross-tab sync)
window.addEventListener('storage', applyUserToHeader);
