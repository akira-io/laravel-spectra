<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Spectra - API Inspector</title>
    <script>
        window.spectraConfig = {
            appName: "{{ config('app.name', 'Laravel') }}",
            appEnv: "{{ app()->environment() }}",
            phpVersion: "{{ PHP_VERSION }}",
            laravelVersion: "{{ app()->version() }}"
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @if(isset($page['props']['assets']))
        <link rel="stylesheet" href="{{ $page['props']['assets']['css'] }}">
        <script type="module" src="{{ $page['props']['assets']['js'] }}"></script>
    @endif

    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
