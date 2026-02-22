<x-layouts.app :titel="'Audit-Log'">

<div style="margin-bottom: 1.25rem;">
    <p class="text-klein text-hell" style="margin: 0;">
        Vollständiges Protokoll aller Zugriffe und Änderungen an medizinischen Daten.
    </p>
</div>

{{-- Filter --}}
<div class="karte" style="margin-bottom: 1.25rem; padding: 1rem;">
    <form method="GET" action="{{ route('audit.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label class="feld-label" style="font-size: 0.75rem;">Benutzer (E-Mail)</label>
            <input type="text" name="benutzer" class="feld" style="width: 180px;" value="{{ request('benutzer') }}" placeholder="@beispiel.ch">
        </div>
        <div>
            <label class="feld-label" style="font-size: 0.75rem;">Aktion</label>
            <select name="aktion" class="feld" style="width: 140px;">
                <option value="">Alle</option>
                @foreach(['login','logout','erstellt','geaendert','geloescht','angezeigt'] as $a)
                    <option value="{{ $a }}" {{ request('aktion') === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="feld-label" style="font-size: 0.75rem;">Datensatz-Typ</label>
            <select name="modell" class="feld" style="width: 140px;">
                <option value="">Alle</option>
                @foreach(['Klient','Einsatz','Rechnung','Benutzer','Organisation'] as $m)
                    <option value="{{ $m }}" {{ request('modell') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="feld-label" style="font-size: 0.75rem;">Von</label>
            <input type="date" name="von" class="feld" style="width: 140px;" value="{{ request('von') }}">
        </div>
        <div>
            <label class="feld-label" style="font-size: 0.75rem;">Bis</label>
            <input type="date" name="bis" class="feld" style="width: 140px;" value="{{ request('bis') }}">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primaer">Filtern</button>
            <a href="{{ route('audit.index') }}" class="btn btn-sekundaer">Zurücksetzen</a>
        </div>
    </form>
</div>

{{-- Tabelle --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Zeitpunkt</th>
                <th>Benutzer</th>
                <th>Aktion</th>
                <th>Datensatz</th>
                <th>Beschreibung</th>
                <th>IP-Adresse</th>
                <th style="width: 2rem;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-klein text-hell" style="white-space: nowrap;">
                    {{ $log->created_at->format('d.m.Y H:i:s') }}
                </td>
                <td style="font-size: 0.8125rem;">
                    <div class="text-mittel">{{ $log->benutzer_name ?? '—' }}</div>
                    <div class="text-mini text-hell">{{ $log->benutzer_email ?? 'System' }}</div>
                </td>
                <td>
                    @php
                        $badgeKlasse = match($log->aktion) {
                            'login'      => 'badge-erfolg',
                            'logout'     => 'badge-grau',
                            'erstellt'   => 'badge-info',
                            'geaendert'  => 'badge-warnung',
                            'geloescht'  => 'badge-fehler',
                            'angezeigt'  => 'badge-grau',
                            default      => 'badge-grau',
                        };
                    @endphp
                    <span class="badge {{ $badgeKlasse }}">{{ $log->aktion }}</span>
                </td>
                <td style="font-size: 0.8125rem;">
                    @if($log->modell_typ)
                        <span class="text-hell">{{ $log->modell_typ }}</span>
                        @if($log->modell_id)
                            <span class="text-hell">#{{ $log->modell_id }}</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td style="font-size: 0.8125rem;">{{ $log->beschreibung ?? '—' }}</td>
                <td class="text-mini text-hell" style="font-family: monospace;">
                    {{ $log->ip_adresse ?? '—' }}
                </td>
                <td>
                    @if($log->alte_werte || $log->neue_werte)
                        <button type="button"
                            style="background: none; border: none; cursor: pointer; color: var(--cs-primaer); font-size: 0.875rem;"
                            onclick="toggleDetail({{ $log->id }})"
                            title="Details anzeigen">⋯</button>
                    @endif
                </td>
            </tr>
            {{-- Detail-Zeile --}}
            @if($log->alte_werte || $log->neue_werte)
            <tr id="detail-{{ $log->id }}" style="display: none; background-color: #f8fafc;">
                <td colspan="7" style="padding: 1rem; font-size: 0.8125rem;">
                    <div class="form-grid-2" style="gap: 1rem;">
                        @if($log->alte_werte)
                        <div>
                            <div class="text-fett" style="color: var(--cs-fehler); margin-bottom: 0.5rem;">Vorher</div>
                            @foreach($log->alte_werte as $feld => $wert)
                            <div style="display: flex; gap: 0.5rem; padding: 0.2rem 0; border-bottom: 1px solid var(--cs-border);">
                                <span class="text-hell" style="min-width: 150px;">{{ $feld }}</span>
                                <span style="font-family: monospace;">{{ is_array($wert) ? json_encode($wert) : $wert }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @if($log->neue_werte)
                        <div>
                            <div class="text-fett" style="color: var(--cs-erfolg); margin-bottom: 0.5rem;">Nachher</div>
                            @foreach($log->neue_werte as $feld => $wert)
                            <div style="display: flex; gap: 0.5rem; padding: 0.2rem 0; border-bottom: 1px solid var(--cs-border);">
                                <span class="text-hell" style="min-width: 150px;">{{ $feld }}</span>
                                <span style="font-family: monospace;">{{ is_array($wert) ? json_encode($wert) : $wert }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="7" class="text-mitte text-hell" style="padding: 2rem;">
                    Keine Einträge gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
<div style="margin-top: 1rem;">
    {{ $logs->links() }}
</div>
@endif

@push('scripts')
<script>
function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endpush

</x-layouts.app>
