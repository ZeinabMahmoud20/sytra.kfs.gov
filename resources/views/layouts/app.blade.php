<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'الشبكة الوطنية للطوارئ - محافظة كفر الشيخ')</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/imgs/watania2.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Tailwind الحقيقي المبني عن طريق Vite، بدل رابط الـ CDN القديم --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-bg-soft text-slate-800 font-cairo">

    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    @include('partials.sidebar')

    <div class="lg:mr-72 min-h-screen flex flex-col transition-all duration-300">

        @include('partials.header')

        <main class="flex-1 p-6 sm:p-10">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl font-bold flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- مكان محتوى كل صفحة --}}
            @yield('content')
        </main>

        <footer class="p-6 text-center text-slate-400 text-sm border-t border-slate-200 bg-white/50">
            <p>جميع الحقوق محفوظة &copy; {{ date('Y') }} - الشبكة الوطنية للطوارئ بكفر الشيخ</p>
        </footer>
    </div>

    @include('partials.logout-modal')

    {{-- سكريبت مشترك لكل الصفحات: فتح/قفل السايدبار، الدروب داون، المودال --}}
    <script src="{{ asset('assets/js/layout.js') }}"></script>

    {{-- أي سكريبت خاص بصفحة معينة يتحط هنا --}}
    @stack('scripts')

    @include('partials.tmam-reminder')
</body>

</html>