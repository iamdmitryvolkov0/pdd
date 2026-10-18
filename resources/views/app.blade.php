<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f1e8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/brand-mark.svg" type="image/svg+xml">
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const dark = savedTheme === 'dark' || (!savedTheme && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.classList.toggle('light', !dark);
        })();
    </script>
    <title inertia>{{ config('app.name') }}</title>
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="min-h-full">@inertia</body>
</html>
