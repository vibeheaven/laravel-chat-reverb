<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Chat</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['themes/tailwind/css/app.css', 'themes/tailwind/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div id="app" class="min-h-screen bg-gray-100">
        <chat-dashboard :current-user="{{ json_encode([
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'avatar' => auth()->user()->avatar_url,
        ]) }}"></chat-dashboard>
    </div>
</body>

</html>
