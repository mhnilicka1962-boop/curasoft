<x-layouts.app :titel="$klient->vorname . ' ' . $klient->nachname . ' — Bearbeiten'">

<div style="max-width: 720px;">
    <a href="{{ route('klienten.show', $klient) }}" class="text-klein link-gedaempt" style="display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 1.25rem;">
        ← Zurück zum Klient
    </a>

    <form method="POST" action="{{ route('klienten.update', $klient) }}">
        @csrf
        @method('PUT')
        @include('klienten._formular', ['klient' => $klient])

        {{-- Aktiv/Inaktiv --}}
        <div class="karte" style="margin-top: 1rem; padding: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.625rem; cursor: pointer; font-size: 0.875rem; font-weight: 500;">
                <input type="hidden" name="aktiv" value="0">
                <input type="checkbox" name="aktiv" value="1" style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);"
                    {{ $klient->aktiv ? 'checked' : '' }}>
                Klient aktiv
            </label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('klienten.show', $klient) }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
            @if($klient->aktiv)
            <form method="POST" action="{{ route('klienten.destroy', $klient) }}" onsubmit="return confirm('Klient wirklich deaktivieren?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-gefahr" style="font-size: 0.8125rem;">Deaktivieren</button>
            </form>
            @endif
        </div>
    </form>
</div>

</x-layouts.app>
