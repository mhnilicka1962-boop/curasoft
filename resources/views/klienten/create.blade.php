<x-layouts.app :titel="'Neuer Klient'">

<div style="max-width: 720px;">
    <a href="{{ route('klienten.index') }}" class="text-klein link-gedaempt" style="display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 1.25rem;">
        ← Zurück zur Liste
    </a>

    <form method="POST" action="{{ route('klienten.store') }}">
        @csrf
        @include('klienten._formular')
        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primaer">Klient anlegen</button>
            <a href="{{ route('klienten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>
</div>

</x-layouts.app>
