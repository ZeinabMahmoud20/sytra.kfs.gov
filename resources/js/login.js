document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const loginInputs = loginForm.querySelectorAll('input[required]');
        const rememberMe = document.getElementById('rememberMe');

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
                window.location.href = '../dashboard/dashboard.html';
            }
        });
    }
});
