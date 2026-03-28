<x-layouts.app titel="Mitarbeitende">

<div class="seiten-kopf">
    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Mitarbeitende</h1>
    <a href="{{ route('mitarbeiter.create') }}" class="btn btn-primaer">+ Neu</a>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif


{{-- Filter --}}
<div class="karte" style="padding: 0.75rem 1rem; margin-bottom: 1rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label class="feld-label">Suche</label>
            <input type="text" name="suche" class="feld" value="{{ request('suche') }}" placeholder="Name, E-Mail…" style="min-width: 200px;">
        </div>
        <div>
            <label class="feld-label">Rolle</label>
            <select name="rolle" class="feld">
                <option value="">Alle</option>
                <option value="admin"       {{ request('rolle') === 'admin'        ? 'selected' : '' }}>Admin</option>
                <option value="pflege"      {{ request('rolle') === 'pflege'       ? 'selected' : '' }}>Pflege</option>
                <option value="buchhaltung" {{ request('rolle') === 'buchhaltung'  ? 'selected' : '' }}>Buchhaltung</option>
            </select>
        </div>
        <div>
            <label class="feld-label">Status</label>
            <select name="aktiv" class="feld">
                <option value="1" {{ !request()->exists('aktiv') || request('aktiv') === '1' ? 'selected' : '' }}>Aktiv</option>
                <option value="0" {{ request('aktiv') === '0' ? 'selected' : '' }}>Inaktiv</option>
                <option value=""  {{ request()->exists('aktiv') && request('aktiv') === '' ? 'selected' : '' }}>Alle</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sekundaer">Filtern</button>
        <a href="{{ route('mitarbeiter.index') }}" class="btn btn-sekundaer">Zurücksetzen</a>
    </form>
</div>

{{-- Tabelle --}}
<div class="karte-null" style="overflow-x: auto;">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Telefon</th>
                <th class="text-mitte">Pensum</th>
                <th>Rolle</th>
                <th>Qualifikationen</th>
                <th>Eintritt</th>
                <th class="text-mitte">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($mitarbeiter as $ma)
            <tr style="{{ !$ma->aktiv ? 'opacity: 0.5;' : '' }}">
                <td class="text-fett">
                    <a href="{{ route('mitarbeiter.show', $ma) }}" class="link-primaer">
                        {{ $ma->nachname }} {{ $ma->vorname }}
                    </a>
                    @if($ma->anstellungsart === 'angehoerig')
                        <span class="badge badge-info" style="font-size:0.65rem; margin-left:0.25rem;">Pfl. Angehöriger</span>
                    @endif
                </td>
                <td style="font-size: 0.8125rem;">{{ $ma->email }}</td>
                <td style="font-size: 0.8125rem;">{{ $ma->telefon ?? '—' }}</td>
                <td class="text-mitte" style="font-size: 0.8125rem;">{{ $ma->pensum }}%</td>
                <td>
                    @php $rolleKlasse = match($ma->rolle) { 'admin' => 'badge-fehler', 'buchhaltung' => 'badge-info', default => 'badge-primaer' }; @endphp
                    <span class="badge {{ $rolleKlasse }}">{{ ucfirst($ma->rolle) }}</span>
                </td>
                <td style="font-size: 0.75rem;">
                    @foreach($ma->qualifikationen->take(3) as $q)
                        <span class="badge badge-grau" style="font-size: 0.65rem;">{{ $q->kuerzel ?: $q->bezeichnung }}</span>
                    @endforeach
                    @if($ma->qualifikationen->count() > 3)
                        <span class="text-mini text-hell">+{{ $ma->qualifikationen->count() - 3 }}</span>
                    @endif
                </td>
                <td style="font-size: 0.8125rem;">{{ $ma->eintrittsdatum?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-mitte">
                    @if(!$ma->aktiv)
                        <span class="badge badge-grau">Inaktiv</span>
                    @elseif($ma->einladungs_token && $ma->einladungs_token_ablauf?->isFuture())
                        <span class="badge badge-warnung">Einladung offen</span>
                    @else
                        <span class="badge badge-erfolg">Aktiv</span>
                    @endif
                </td>
                <td style="white-space: nowrap;">
                    <a href="{{ route('mitarbeiter.show', $ma) }}" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✏</a>
                    @if($ma->einladungs_token && $ma->einladungs_token_ablauf?->isFuture())
                        <form method="POST" action="{{ route('mitarbeiter.einladung', $ma) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Einladung erneut senden">📧</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-mitte text-hell" style="padding: 2rem;">Keine Mitarbeitenden gefunden.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($mitarbeiter->hasPages())
    <div style="margin-top: 1rem;">{{ $mitarbeiter->links() }}</div>
@endif


</x-layouts.app>
