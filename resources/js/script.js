/**
 * script.js - Interactive elements for the National Emergency Network project
 */

document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('header');
    const themeButtons = document.querySelectorAll('.theme-toggle');
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    // Theme Toggle Logic
    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateAllThemeIcons(currentTheme);

    themeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateAllThemeIcons(newTheme);
        });
    });

    function updateAllThemeIcons(theme) {
        document.querySelectorAll('.theme-toggle i').forEach(icon => {
            if (theme === 'light') {
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    }

    // Mobile Menu Toggle logic
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            const icon = menuToggle.querySelector('i');
            if (mainNav.classList.contains('active')) {
                icon.classList.replace('fa-bars', 'fa-times');
            } else {
                icon.classList.replace('fa-times', 'fa-bars');
            }
        });
    }

    // --- Shared Auth Logic ---
    function validateField(input) {
        const group = input.closest('.form-group');
        if (!group) return true;

        let valid = true;
        const val = input.value.trim();

        if (input.required && val === '') {
            valid = false;
        } else if (val !== '') {
            if (input.type === 'email') {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                valid = re.test(val);
            } else if (input.id === 'mobile') {
                const re = /^01[0125][0-9]{8}$/;
                valid = re.test(val);
            } else if (input.id === 'nationalId') {
                valid = val.length === 14 && /^\d+$/.test(val);
            }
        }

        if (valid) {
            group.classList.remove('error');
            group.classList.add('success');
        } else {
            group.classList.remove('success');
            group.classList.add('error');
        }
        return valid;
    }

    // Password Toggle (Works for both Login & Register)
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            const passwordInput = this.parentElement.querySelector('input');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // --- Registration Form Logic ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const inputs = registerForm.querySelectorAll('input[required]');
        const profileImageInput = document.getElementById('profileImage');
        const profilePreview = document.getElementById('profilePreview');
        const removeProfileBtn = document.getElementById('removeProfileImage');

        // Profile Image Preview
        if (profilePreview && profileImageInput) {
            profilePreview.addEventListener('click', () => profileImageInput.click());
            profileImageInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profilePreview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                        if (removeProfileBtn) removeProfileBtn.style.display = 'flex';
                    }
                    reader.readAsDataURL(file);
                }
            });

            if (removeProfileBtn) {
                removeProfileBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileImageInput.value = '';
                    profilePreview.innerHTML = `<i class="fas fa-user"></i><div class="upload-overlay"><i class="fas fa-camera"></i></div>`;
                    removeProfileBtn.style.display = 'none';
                });
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', () => validateField(input));
        });

        const confirmPasswordBody = document.getElementById('confirmPassword');
        if (confirmPasswordBody) {
            confirmPasswordBody.addEventListener('input', () => {
                const password = document.getElementById('password');
                const group = confirmPasswordBody.closest('.form-group');
                if (confirmPasswordBody.value && confirmPasswordBody.value === password.value) {
                    group.classList.remove('error');
                    group.classList.add('success');
                } else {
                    group.classList.remove('success');
                    group.classList.add('error');
                }
            });
        }

        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });

            const password = document.getElementById('password');
            const confirm = document.getElementById('confirmPassword');
            if (confirm && confirm.value !== password.value) isValid = false;

            if (isValid) {
                alert('تم إنشاء الحساب بنجاح! جاري تحويلك لصفحة تسجيل الدخول...');
                window.location.href = 'login.html';
            }
        });
    }

    // --- Login Form Logic ---
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const loginInputs = loginForm.querySelectorAll('input[required]');
        const rememberMe = document.getElementById('rememberMe');

        // Load remembered user
        const savedUser = localStorage.getItem('rememberedUser');
        if (savedUser) {
            const data = JSON.parse(savedUser);
            if (loginForm.querySelector('#email')) loginForm.querySelector('#email').value = data.email || '';
            if (loginForm.querySelector('#nationalId')) loginForm.querySelector('#nationalId').value = data.id || '';
            if (rememberMe) rememberMe.checked = true;
        }

        loginInputs.forEach(input => {
            input.addEventListener('input', () => validateField(input));
        });

        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            let isValid = true;
            loginInputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });

            if (isValid) {
                if (rememberMe && rememberMe.checked) {
                    const userData = {
                        email: loginForm.querySelector('#email').value,
                        id: loginForm.querySelector('#nationalId').value
                    };
                    localStorage.setItem('rememberedUser', JSON.stringify(userData));
                } else {
                    localStorage.removeItem('rememberedUser');
                }

                alert('تم تسجيل الدخول بنجاح! جاري تحويلك للوحة التحكم...');
                window.location.href = 'dashboard.html';
            }
        });
    }

    // Smooth header appearance on scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.style.padding = '0.3rem 5%';
            header.style.backgroundColor = 'var(--header-bg-scroll)';
        } else {
            header.style.padding = '0.5rem 5%';
            header.style.backgroundColor = 'var(--glass-bg)';
        }
    });
});
