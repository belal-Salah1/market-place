<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        {{-- Guests are the top of the funnel, so they get the Pixel too; only
             admin and vendor back-office traffic stays out of the reporting. --}}
        @unless (in_array(auth()->user()?->role?->name, [
            \App\Enums\RoleStatus::ADMIN->value,
            \App\Enums\RoleStatus::VENDOR->value,
        ], true))
            @include('partials.meta-pixel')
        @endunless
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
