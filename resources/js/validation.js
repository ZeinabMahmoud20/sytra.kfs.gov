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

document.addEventListener('DOMContentLoaded', () => {
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
});
