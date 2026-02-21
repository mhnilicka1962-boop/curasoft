@php
    $logoPfad = $logoPfad ?? config('theme.logo');
    $appName  = config('theme.app_name');
@endphp

@if($logoPfad)
    <img src="{{ asset($logoPfad) }}"
         alt="{{ $appName }}"
         style="max-height: 36px; max-width: 160px; object-fit: contain;">
@else
    <span class="logo-text">{{ $appName }}</span>
@endif
