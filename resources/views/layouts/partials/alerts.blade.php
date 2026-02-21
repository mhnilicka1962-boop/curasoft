@if(session('erfolg'))
    <div class="alert alert-erfolg">{{ session('erfolg') }}</div>
@endif
@if(session('fehler'))
    <div class="alert alert-fehler">{{ session('fehler') }}</div>
@endif
@if(session('warnung'))
    <div class="alert alert-warnung">{{ session('warnung') }}</div>
@endif
