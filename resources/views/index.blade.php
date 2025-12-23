<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel API Inspector{{ config('app.name') ? ' - ' . config('app.name') : '' }}</title>

     {{-- Package CSS --}}
    <link
        rel="stylesheet"
        href="{{ asset(config('api-inspector.assets_path') . '/css/app.css') }}"/>
</head>

<body class="h-full px-3 lg:px-5 bg-gray-100 dark:bg-gray-900">
<div id="api-inspector"></div>

<!-- Global API Inspector Object -->
<script>
    window.ApiInspector = @json($lapiScriptVariables);

    // Add additional headers for ApiInspector requests like so:
    // window.ApiInspector.headers['Authorization'] = 'Bearer xxxxxxx';
</script>
{{-- Package JS --}}
    <script
        src="{{ asset(config('api-inspector.assets_path') . '/js/app.js') }}"
        defer
    ></script>
</body>
</html>