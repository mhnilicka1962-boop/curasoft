<x-layouts.app titel="Neue Krankenkasse">
<div style="max-width: 600px;">

    <a href="{{ route('krankenkassen.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Krankenkassen</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">Neue Krankenkasse</h1>

    <div class="karte">
        <form method="POST" action="{{ route('krankenkassen.store') }}">
            @csrf
            @include('stammdaten.krankenkassen._formular')
            <div class="abschnitt-trenn" style="margin-top: 1rem; padding-top: 1rem;">
                <button type="submit" class="btn btn-primaer">Krankenkasse speichern</button>
                <a href="{{ route('krankenkassen.index') }}" class="btn btn-sekundaer" style="margin-left: 0.5rem;">Abbrechen</a>
            </div>
        </form>
    </div>

</div>
</x-layouts.app>
