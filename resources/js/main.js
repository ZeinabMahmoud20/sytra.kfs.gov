document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('header');
    const themeButtons = document.querySelectorAll('.theme-toggle');
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.querySelector('.main-nav');

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

    window.addEventListener('scroll', () => {
        if (header) {
            if (window.scrollY > 50) {
                header.style.padding = '0.3rem 5%';
                header.style.backgroundColor = 'var(--header-bg-scroll)';
            } else {
                header.style.padding = '0.5rem 5%';
                header.style.backgroundColor = 'var(--glass-bg)';
            }
        }
    });
});
