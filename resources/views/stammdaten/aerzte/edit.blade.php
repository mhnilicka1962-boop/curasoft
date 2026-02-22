<x-layouts.app :titel="'Arzt: ' . $arzt->vollname()">
<div style="max-width: 700px;">

    <a href="{{ route('aerzte.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Ärzte</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">{{ $arzt->vollname() }}</h1>

    <div class="karte">
        <form method="POST" action="{{ route('aerzte.update', $arzt) }}">
            @csrf @method('PUT')
            @include('stammdaten.aerzte._formular')
            <div class="abschnitt-trenn" style="margin-top: 1rem; padding-top: 1rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('aerzte.index') }}" class="btn btn-sekundaer" style="margin-left: 0.5rem;">Abbrechen</a>
            </div>
        </form>
    </div>

</div>
</x-layouts.app>
