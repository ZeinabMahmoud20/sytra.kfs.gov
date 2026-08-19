<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite([
        'resources/css/app.css',
        'resources/css/style.css',
        'resources/css/login.css',
        'resources/js/app.js',
        'resources/js/main.js',
        'resources/js/login.js',
    ])

    @livewireStyles
</head>

<body>
    
    <header>
        <div class="header-content">
            <div class="brand">
                <a href="{{ url('/') }}" class="brand-link">
                    <img src="{{ asset('assets/imgs/kfslogo.jpg') }}"
                         alt="شعار المحافظة"
                         class="logo header-main-logo">

                    <div class="brand-text">
                        <span class="name">جمهورية مصر العربية</span>
                        <span class="location">محافظة كفر الشيخ</span>
                    </div>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li class="mobile-only">
                        <button type="button" class="theme-toggle mobile-theme-toggle">
                            <i class="fas fa-moon"></i>
                            <span>تبديل المظهر</span>
                        </button>
                    </li>
                </ul>
            </nav>

            <div class="nav-actions">
                <button type="button" class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <span>تبديل المظهر</span>
                </button>

                <button type="button" class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    {{ $slot }}

    @livewireScripts

</body>

</html>