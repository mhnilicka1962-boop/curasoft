<x-layouts.app :titel="$krankenkasse->name">
<div style="max-width: 600px;">

    <a href="{{ route('krankenkassen.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Krankenkassen</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">{{ $krankenkasse->name }}</h1>

    <div class="karte">
        <form method="POST" action="{{ route('krankenkassen.update', $krankenkasse) }}">
            @csrf @method('PUT')
            @include('stammdaten.krankenkassen._formular')
            <div class="abschnitt-trenn" style="margin-top: 1rem; padding-top: 1rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('krankenkassen.index') }}" class="btn btn-sekundaer" style="margin-left: 0.5rem;">Abbrechen</a>
            </div>
        </form>
    </div>

</div>
</x-layouts.app>
