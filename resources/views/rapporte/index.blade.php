<x-layouts.app titel="Rapporte">
<div style="max-width: 1000px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Rapporte</h1>
        <a href="{{ route('rapporte.create') }}" class="btn btn-primaer">+ Neuer Rapport</a>
    </div>

    {{-- Filter --}}
    <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <select name="klient_id" class="feld" style="min-width: 180px; flex: 1;">
            <option value="">Alle Klienten</option>
            @foreach($klienten as $k)
                <option value="{{ $k->id }}" {{ request('klient_id') == $k->id ? 'selected' : '' }}>{{ $k->vollname() }}</option>
            @endforeach
        </select>
        <select name="typ" class="feld" style="width: 180px;">
            <option value="">Alle Typen</option>
            @foreach(\App\Models\Rapport::$typen as $wert => $lbl)
                <option value="{{ $wert }}" {{ request('typ') === $wert ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        @if(auth()->user()->rolle === 'admin')
        <select name="benutzer_id" class="feld" style="width: 160px;">
            <option value="">Alle Mitarbeiter</option>
            @foreach($mitarbeiter as $m)
                <option value="{{ $m->id }}" {{ request('benutzer_id') == $m->id ? 'selected' : '' }}>{{ $m->vorname }} {{ $m->nachname }}</option>
            @endforeach
        </select>
        @endif
        <input type="date" name="datum_von" class="feld" style="width: 140px;" value="{{ request('datum_von') }}" placeholder="Von">
        <input type="date" name="datum_bis" class="feld" style="width: 140px;" value="{{ request('datum_bis') }}" placeholder="Bis">
        <button type="submit" class="btn btn-sekundaer">Filtern</button>
        @if(request()->hasAny(['klient_id','typ','benutzer_id','datum_von','datum_bis']))
            <a href="{{ route('rapporte.index') }}" class="btn btn-sekundaer">×</a>
        @endif
    </form>

    <div class="karte-null">
        <div class="tabelle-wrapper">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <th class="abschnitt-label" style="padding: 0.625rem 0.875rem; text-align: left; white-space: nowrap;">Datum</th>
                    <th class="abschnitt-label" style="padding: 0.625rem 0.875rem; text-align: left;">Klient</th>
                    <th class="abschnitt-label" style="padding: 0.625rem 0.875rem; text-align: left;">Typ</th>
                    <th class="col-desktop abschnitt-label" style="padding: 0.625rem 0.875rem; text-align: left;">Inhalt</th>
                    <th class="col-desktop abschnitt-label" style="padding: 0.625rem 0.875rem; text-align: left;">Von</th>
                    <th style="padding: 0.625rem 0.875rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapporte as $r)
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <td class="text-hell" style="padding: 0.625rem 0.875rem; white-space: nowrap;">
                        {{ $r->datum->format('d.m.Y') }}
                        @if($r->zeit_von)
                            <div style="font-size: 0.8rem;">{{ $r->zeit_von }}</div>
                        @endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;">
                        <a href="{{ route('klienten.show', $r->klient) }}" class="link-primaer text-fett">
                            {{ $r->klient->vollname() }}
                        </a>
                        <span class="mobile-meta">
                            {{ \App\Models\Rapport::$typen[$r->rapport_typ] ?? $r->rapport_typ }}
                            · {{ $r->benutzer?->vorname }}
                        </span>
                    </td>
                    <td style="padding: 0.625rem 0.875rem;">
                        <span class="badge {{ $r->rapport_typ === 'zwischenfall' ? 'badge-fehler' : ($r->rapport_typ === 'pflege' ? 'badge-primaer' : 'badge-grau') }} text-mini">
                            {{ \App\Models\Rapport::$typen[$r->rapport_typ] ?? $r->rapport_typ }}
                        </span>
                        @if($r->vertraulich)
                            <span class="badge badge-warnung" style="font-size: 0.7rem; margin-left: 0.25rem;">Vertraulich</span>
                        @endif
                    </td>
                    <td class="col-desktop text-hell" style="padding: 0.625rem 0.875rem; max-width: 300px;">
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Str::limit($r->inhalt, 80) }}</div>
                    </td>
                    <td class="col-desktop text-klein text-hell" style="padding: 0.625rem 0.875rem;">
                        {{ $r->benutzer?->vorname }} {{ $r->benutzer?->nachname }}
                    </td>
                    <td class="text-rechts" style="padding: 0.625rem 0.875rem;">
                        <a href="{{ route('rapporte.show', $r) }}" class="link-primaer" style="font-size: 0.8125rem;">Detail →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-hell text-mitte" style="padding: 2rem;">
                        Keine Rapporte gefunden.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($rapporte->hasPages())
        <div style="margin-top: 1rem;">{{ $rapporte->links() }}</div>
    @endif

</div>
</x-layouts.app>
