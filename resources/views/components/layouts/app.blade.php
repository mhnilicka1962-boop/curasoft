<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ?? 'Dashboard' }} — {{ config('theme.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(config('theme.farbe_primaer'))
    <style>
        :root {
            --cs-primaer: {{ config('theme.farbe_primaer') }};
            --cs-primaer-dunkel: {{ config('theme.farbe_primaer_dunkel') }};
            --cs-primaer-hell: {{ config('theme.farbe_primaer_hell') }};
        }
    </style>
    @endif

    @stack('styles')
</head>
<body>

@php $layout = config('theme.layout', 'sidebar'); @endphp

@if($layout === 'topnav')
    @include('layouts.partials.topnav')
@else
    @include('layouts.partials.sidebar')
@endif

@stack('scripts')

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('offen');
}
</script>

</body>
</html>
