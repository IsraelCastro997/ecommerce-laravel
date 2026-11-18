<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>Acceso administrativo · Refugio Outdoor</title>
    @vite('resources/js/app.js')
    @livewireStyles
</head>
<body class="refugio" x-data="themePicker">
    <main class="store-wrap blade-page">
        <section class="blade-auth blade-panel" aria-labelledby="admin-login-heading">
            <p class="eyebrow">Refugio Outdoor</p>
            <h1 id="admin-login-heading">Acceso administrativo</h1>
            <p class="page-note mb-5">Ingresa con una cuenta autorizada para administrar la tienda.</p>
            <form class="blade-form" method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <x-form-field name="email" label="Correo electrónico" type="email" autocomplete="username" autofocus />
                <x-form-field name="password" label="Contraseña" type="password" autocomplete="current-password" />
                <label class="flex-row items-center"><input type="checkbox" name="remember" value="1"> Mantener mi sesión</label>
                <button type="submit" class="store-button primary">Entrar a administración</button>
            </form>
            <a class="text-button mt-5" href="{{ route('home') }}">Volver a la tienda</a>
        </section>
    </main>
    @livewireScripts
</body>
</html>
