<x-layouts.app titel="Mitarbeitende">

<div class="seiten-kopf">
    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Mitarbeitende</h1>
    <button type="button" onclick="oeffneMAModal()" class="btn btn-primaer">+ Neu</button>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif

{{-- Modal: Neuer Mitarbeiter --}}
<div id="ma-modal" style="display:none; position:fixed; inset:0; z-index:500; background:rgba(0,0,0,.45); overflow-y:auto;">
    <div style="margin:2rem auto; max-width:680px; background:#fff; border-radius:var(--cs-radius); box-shadow:0 8px 40px rgba(0,0,0,.18); padding:1.5rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <div style="font-size:1rem; font-weight:700;">Neuer Mitarbeiter</div>
            <button onclick="schliesseMAModal()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:var(--cs-text-hell); line-height:1;">×</button>
        </div>

        @if($errors->any())
        <div class="fehler-box" style="margin-bottom:1rem;">
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('mitarbeiter.store') }}">
            @csrf
            <div class="form-grid" style="margin-bottom:0.75rem;">
                <div>
                    <label class="feld-label">Anrede</label>
                    <select name="anrede" class="feld">
                        <option value="">—</option>
                        <option value="Herr"  {{ old('anrede') === 'Herr'  ? 'selected' : '' }}>Herr</option>
                        <option value="Frau"  {{ old('anrede') === 'Frau'  ? 'selected' : '' }}>Frau</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Vorname *</label>
                    <input type="text" name="vorname" class="feld" required value="{{ old('vorname') }}">
                    @error('vorname')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Nachname *</label>
                    <input type="text" name="nachname" class="feld" required value="{{ old('nachname') }}">
                    @error('nachname')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">E-Mail *</label>
                    <input type="email" name="email" class="feld" required value="{{ old('email') }}">
                    @error('email')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Telefon</label>
                    <input type="text" name="telefon" class="feld" value="{{ old('telefon') }}">
                </div>
                <div>
                    <label class="feld-label">Rolle *</label>
                    <select name="rolle" class="feld" required>
                        <option value="pflege"      {{ old('rolle', 'pflege') === 'pflege'      ? 'selected' : '' }}>Pflege</option>
                        <option value="buchhaltung" {{ old('rolle') === 'buchhaltung'           ? 'selected' : '' }}>Buchhaltung</option>
                        <option value="admin"       {{ old('rolle') === 'admin'                 ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Anstellungsart</label>
                    <select name="anstellungsart" class="feld">
                        <option value="fachperson"  {{ old('anstellungsart', 'fachperson') === 'fachperson'  ? 'selected' : '' }}>Fachperson</option>
                        <option value="angehoerig"  {{ old('anstellungsart') === 'angehoerig'                ? 'selected' : '' }}>Pflegender Angehöriger</option>
                        <option value="freiwillig"  {{ old('anstellungsart') === 'freiwillig'               ? 'selected' : '' }}>Freiwillig</option>
                        <option value="praktikum"   {{ old('anstellungsart') === 'praktikum'                ? 'selected' : '' }}>Praktikum</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Pensum %</label>
                    <input type="number" name="pensum" class="feld" min="0" max="100" value="{{ old('pensum', 100) }}">
                </div>
                <div>
                    <label class="feld-label">Eintrittsdatum</label>
                    <input type="date" name="eintrittsdatum" class="feld" value="{{ old('eintrittsdatum') }}">
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primaer">Speichern & Einladen</button>
                <button type="button" onclick="schliesseMAModal()" class="btn btn-sekundaer">Abbrechen</button>
            </div>
        </form>
    </div>
</div>

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

@push('scripts')
<script>
function oeffneMAModal() {
    document.getElementById('ma-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function schliesseMAModal() {
    document.getElementById('ma-modal').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') schliesseMAModal(); });
@if($errors->any()) oeffneMAModal(); @endif
</script>
@endpush

</x-layouts.app>
