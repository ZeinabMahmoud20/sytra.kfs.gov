<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="البوابة الرسمية للشبكة الوطنية للطوارئ بكفر الشيخ - خدمات طوارئ سريعة وفعالة">

    <title>الشبكة الوطنية للطوارئ - محافظة كفر الشيخ</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/imgs/watania2.png') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite([
        'resources/css/app.css',
        'resources/css/style.css',
        'resources/js/app.js',
        'resources/js/main.js',
    ])
</head>

<body>

<header>
    <div class="header-content">

        <div class="brand">

            <img src="{{ asset('assets/imgs/kfslogo.jpg') }}"
                 alt="شعار محافظة كفر الشيخ"
                 class="logo header-main-logo">

            <div class="brand-text">
                <span class="name">جمهورية مصر العربية</span>
                <span class="location">محافظة كفر الشيخ</span>
            </div>

        </div>

        <nav class="main-nav">
            <ul>
                <li class="mobile-only">

                    <button class="theme-toggle mobile-theme-toggle">

                        <i class="fas fa-moon"></i>

                        <span>تبديل المظهر</span>

                    </button>

                </li>
            </ul>
        </nav>

        <div class="nav-actions">

            <button class="theme-toggle"
                    id="theme-toggle"
                    title="تغيير المظهر">

                <i class="fas fa-moon"></i>

                <span>تبديل المظهر</span>

            </button>

            <button class="menu-toggle"
                    id="menu-toggle">

                <i class="fas fa-bars"></i>

            </button>

        </div>

    </div>
</header>

<main>

    <section class="hero">

        <div class="hero-content">

            <div class="hero-logo-container">

                <img src="{{ asset('assets/imgs/watania.png') }}"
                     alt="الشبكة الوطنية"
                     class="hero-network-logo">

                <h1 class="hero-main-title">

                    مرحباً بكم في

                    <span class="highlight">

                        الشبكة الوطنية للطوارئ

                    </span>

                </h1>

                <p class="hero-sub-title">

                    مركز سيطرة كفر الشيخ

                </p>

                <a href="{{ route('login') }}"
                   class="btn-register">

                    تسجيل الدخول

                </a>

            </div>

        </div>

    </section>

</main>

</body>

</html>