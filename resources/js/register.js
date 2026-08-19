document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const inputs = registerForm.querySelectorAll('input[required]');
        const profileImageInput = document.getElementById('profileImage');
        const profilePreview = document.getElementById('profilePreview');
        const removeProfileBtn = document.getElementById('removeProfileImage');

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
});
