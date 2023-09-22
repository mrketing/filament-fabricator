@props([
    'title' => null,
    'meta' => null,
    'slug' => null,
    'name' => null,
    'image' => null,
    'dir' => 'ltr',
])


<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir }}" class="filament-fabricator">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <meta property="og:description" content="{{ $meta ? "{$meta} - " : null }} {{ config('app.name') }}"> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/min/tiny-slider.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <meta property="og:image" content="https://feralpisalo.demo.misterketing.it/storage/{{ $image }}" />
    <meta property="og:title" content="{{ $name }} | Feralpisalò" />
    <meta property="og:description" content="{{ $meta }}" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <meta property="og:url" content="{{ $slug }}" /> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/tiny-slider.css">
    <link rel="stylesheet" href="https://use.typekit.net/wvg4ncf.css">
    @vite(['resources/sass/_mediaqueries.scss', 'resources/sass/bootstrap/_functions.scss', 'resources/sass/_variables.scss', 'resources/css/app.css', 'resources/sass/app.scss', 'resources/css/fontawesome.min.css', 'resources/js/app.js', 'resources/js/custom.js', 'resources/css/bootstrap-grid.css'])
    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.head.start') }}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon-32x32.png') }}" type="image/x-icon">
    @if ($favicon = \Z3d0X\FilamentFabricator\Facades\FilamentFabricator::getFavicon())
        <link rel="icon" href="{{ $favicon }}">
    @endif
    <title>{{ $title ? "{$title} | " : null }} {{ config('app.name') }}</title>



    <style>
        [x-cloak=""],
        [x-cloak="x-cloak"],
        [x-cloak="1"] {
            display: none !important;
        }
    </style>


    @foreach (\Z3d0X\FilamentFabricator\Facades\FilamentFabricator::getStyles() as $name => $path)
        @if (\Illuminate\Support\Str::of($path)->startsWith('<'))
            {!! $path !!}
        @else
            <link rel="stylesheet" href="{{ $path }}" />
        @endif
    @endforeach

    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.head.end') }}
</head>

<body class="filament-fabricator-body sponsor single-sponsor" onload="initialize()">
    @include('partials.header')
    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.body.start') }}

    {{ $slot }}

    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.scripts.start') }}

    @foreach (\Z3d0X\FilamentFabricator\Facades\FilamentFabricator::getScripts() as $name => $path)
        @if (\Illuminate\Support\Str::of($path)->startsWith('<'))
            {!! $path !!}
        @else
            <script defer src="{{ $path }}"></script>
        @endif
    @endforeach

    @stack('scripts')

    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.scripts.end') }}

    {{ \Filament\Facades\Filament::renderHook('filament-fabricator.body.end') }}
    @include('partials.footer')
</body>

</html>
