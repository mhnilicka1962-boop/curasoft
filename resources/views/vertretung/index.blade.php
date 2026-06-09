<x-layouts.app titel="Vertretung">
<div style="max-width: 640px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Vertretung</h1>
        <a href="{{ route('vertretung.erstellen') }}" class="btn btn-primaer">+ Neue Vertretung</a>
    </div>

    @if(session('erfolg'))
    <div class="erfolg-box" style="margin-bottom: 1.25rem;">{{ session('erfolg') }}</div>
    @endif

    @if($abwesenheiten->isEmpty())
    <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">
        Keine aktiven Vertretungen.
    </div>
    @else
    @foreach($abwesenheiten as $abw)
    @php $offen = $abw->offeneEinsaetze(); @endphp
    <div class="karte" style="margin-bottom: 0.625rem; border-left: 3px solid {{ $offen > 0 ? 'var(--cs-fehler)' : 'var(--cs-erfolg)' }};">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="font-weight: 600;">{{ $abw->benutzer->vorname }} {{ $abw->benutzer->nachname }}</span>
                <span class="text-hell text-klein" style="margin-left: 0.5rem;">
                    {{ $abw->datum_von->format('d.m.Y') }} – {{ $abw->datum_bis->format('d.m.Y') }}
                </span>
                @if($offen > 0)
                    <span class="badge badge-fehler" style="margin-left: 0.5rem;">🔴 {{ $offen }} nicht übertragen</span>
                @else
                    <span class="badge badge-erfolg" style="margin-left: 0.5rem;">✓ Alle übertragen</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="{{ route('vertretung.vorschau.get', ['benutzer_id' => $abw->benutzer_id, 'datum_von' => $abw->datum_von->format('Y-m-d'), 'datum_bis' => $abw->datum_bis->format('Y-m-d')]) }}"
                   class="btn {{ $offen > 0 ? 'btn-primaer' : 'btn-sekundaer' }}" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">
                    Detail →
                </a>
                <form method="POST" action="{{ route('vertretung.abwesenheit.loeschen', $abw) }}" style="margin: 0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;"
                        onclick="return confirm('Vertretung löschen?')">✕</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    @endif

</div>
</x-layouts.app>
