<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f1e8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name') }}</title>
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="min-h-full">@inertia</body>
</html>
