<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'الشبكة الوطنية الموحدة') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/imgs/watania2.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
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

    {{ $slot }}

    @livewireScripts

</body>

</html>