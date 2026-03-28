<x-layouts.app titel="Tourenplanung">
<div style="max-width: 1000px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">
            @if(auth()->user()->rolle === 'pflege')
                Deine Tour heute
            @else
                Tourenplanung
            @endif
        </h1>
        @if(auth()->user()->rolle !== 'pflege')
        <a href="{{ route('touren.create') }}" class="btn btn-primaer">+ Neue Tour</a>
        @endif
    </div>

    {{-- Tages-Navigation --}}
    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <a href="{{ route('touren.index', array_merge(request()->query(), ['datum' => $datum->copy()->subDay()->format('Y-m-d')])) }}"
            class="btn btn-sekundaer" style="padding: 0.375rem 0.625rem;">‹</a>

        <input type="date" name="datum" class="feld" style="width: 160px;" value="{{ $datum->format('Y-m-d') }}"
            onchange="this.form.submit()">

        <span style="font-size: 1rem; font-weight: 600;">{{ $datum->isoFormat('dddd, D. MMMM YYYY') }}</span>

        <a href="{{ route('touren.index', array_merge(request()->query(), ['datum' => $datum->copy()->addDay()->format('Y-m-d')])) }}"
            class="btn btn-sekundaer" style="padding: 0.375rem 0.625rem;">›</a>

        @if(auth()->user()->rolle === 'admin')
        <select name="benutzer_id" class="feld" style="width: 180px;" onchange="this.form.submit()">
            <option value="">Alle Mitarbeiter</option>
            @foreach($mitarbeiter as $m)
                <option value="{{ $m->id }}" {{ request('benutzer_id') == $m->id ? 'selected' : '' }}>{{ $m->vorname }} {{ $m->nachname }}</option>
            @endforeach
        </select>
        @endif

        @if($datum->isToday())
            <span class="badge badge-erfolg">Heute</span>
        @endif
    </form>

    {{-- Ansicht-Tabs --}}
    @if(auth()->user()->rolle !== 'pflege')
    <div style="display: flex; gap: 0; border-bottom: 2px solid var(--cs-border); margin-bottom: 1.25rem;">
        <button onclick="tourAnsicht('touren')" id="tab-touren"
            style="padding: 0.375rem 1rem; font-size: 0.875rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid var(--cs-primaer); margin-bottom: -2px; cursor: pointer; color: var(--cs-primaer);">
            Touren
        </button>
        <button onclick="tourAnsicht('klienten')" id="tab-klienten"
            style="padding: 0.375rem 1rem; font-size: 0.875rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
            Klienten-Sicht
        </button>
    </div>
    @endif

    {{-- Klienten-Sicht --}}
    @if(auth()->user()->rolle !== 'pflege')
    <div id="panel-klienten" style="display: none;">
        @php
            $alleEinsaetzeTag = $touren->flatMap(fn($t) => $t->einsaetze->map(fn($e) => ['e' => $e, 'tour' => $t]))
                ->concat(collect($ohneTouren->flatten()->map(fn($e) => ['e' => $e, 'tour' => null])))
                ->sortBy(fn($item) => $item['e']->zeit_von ?? '99:99')
                ->values();
        @endphp
        @if($alleEinsaetzeTag->isEmpty())
            <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">Keine Einsätze für diesen Tag.</div>
        @else
        <div class="karte-null">
            <table class="tabelle">
                <thead>
                    <tr>
                        <th>Zeit</th>
                        <th>Klient</th>
                        <th class="col-desktop">Leistungsart</th>
                        <th>Mitarbeiter</th>
                        <th>Tour</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alleEinsaetzeTag as $item)
                    @php $e = $item['e']; $t = $item['tour']; @endphp
                    <tr>
                        <td class="text-hell" style="white-space: nowrap; font-size: 0.8125rem;">
                            {{ $e->zeit_von ? substr($e->zeit_von,0,5) : '—' }}
                        </td>
                        <td>
                            <a href="{{ route('klienten.show', $e->klient_id) }}" class="link-primaer text-mittel">
                                {{ $e->klient?->vollname() }}
                            </a>
                        </td>
                        <td class="col-desktop text-hell" style="font-size: 0.8125rem;">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</td>
                        <td style="font-size: 0.8125rem;">{{ $e->benutzer?->vorname }} {{ $e->benutzer?->nachname }}</td>
                        <td>
                            @if($t)
                                <a href="{{ route('touren.show', $t) }}" class="badge badge-primaer" style="text-decoration: none; font-size: 0.7rem;">
                                    {{ $t->bezeichnung }}
                                </a>
                            @else
                                <span class="badge badge-warnung" style="font-size: 0.7rem;">⚠ Keine Tour</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    <div id="panel-touren">
    @forelse($touren as $tour)
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="seiten-kopf" style="margin-bottom: 0.875rem; gap: 0.5rem;">
            <div>
                <span style="font-weight: 700; font-size: 1rem;">{{ $tour->bezeichnung }}</span>
                <span class="text-klein text-hell" style="margin-left: 0.75rem;">{{ $tour->benutzer?->vorname }} {{ $tour->benutzer?->nachname }}</span>
                @if($tour->start_zeit)
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">{{ $tour->start_zeit }}</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span class="badge {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}" style="font-size: 0.75rem;">
                    {{ ucfirst($tour->status) }}
                </span>
                <a href="{{ route('touren.show', $tour) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">Detail</a>
            </div>
        </div>

        @if($tour->einsaetze->count())
        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
            @foreach($tour->einsaetze as $idx => $e)
            <a href="{{ route('einsaetze.vor-ort', $e) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.625rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); font-size: 0.875rem; text-decoration: none; color: inherit; overflow: hidden;">
                <span class="text-hell" style="font-size: 0.8rem; min-width: 18px; flex-shrink: 0;">{{ $idx + 1 }}.</span>
                <span class="text-fett" style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $e->klient?->vollname() }}</span>
                <span class="text-hell" style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</span>
                @if($e->zeit_von)
                    <span class="text-hell" style="flex-shrink: 0; font-size: 0.8rem;">{{ $e->zeit_von }}</span>
                @endif
                <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem; flex-shrink: 0;">{{ $e->statusLabel() }}</span>
            </a>
            @endforeach
        </div>
        @else
        <p class="text-klein text-hell" style="margin: 0;">Noch keine Einsätze zugewiesen.</p>
        @endif
    </div>
    @empty
    <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">
        @if(auth()->user()->rolle === 'pflege')
            Keine Tour für heute geplant.
            @php
                $eigeneEinsaetze = \App\Models\Einsatz::where('benutzer_id', auth()->id())
                    ->whereDate('datum', $datum)
                    ->with('klient','einsatzLeistungsarten.leistungsart')
                    ->orderBy('zeit_von')
                    ->get();
            @endphp
            @if($eigeneEinsaetze->isNotEmpty())
            <div style="margin-top: 1.25rem; text-align: left;">
                <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Deine Einsätze heute</div>
                <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                    @foreach($eigeneEinsaetze as $e)
                    <a href="{{ route('einsaetze.vor-ort', $e) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); font-size: 0.875rem; text-decoration: none; color: inherit; border: 1px solid var(--cs-border); overflow: hidden;">
                        <span class="text-fett" style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $e->klient?->vollname() }}</span>
                        <span class="text-hell" style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</span>
                        @if($e->zeit_von)
                            <span class="text-hell" style="flex-shrink: 0; font-size: 0.8rem;">{{ substr($e->zeit_von,0,5) }}</span>
                        @endif
                        <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem; flex-shrink: 0;">{{ $e->statusLabel() }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @else
            Keine Touren für diesen Tag.
        @endif
    </div>
    @endforelse

    {{-- Offene Einsätze aus Vergangenheit — nur für pflege --}}
    @if(auth()->user()->rolle === 'pflege')
    @php
        $offeneVergangen = \App\Models\Einsatz::where('benutzer_id', auth()->id())
            ->whereDate('datum', '<', today())
            ->whereIn('status', ['geplant', 'aktiv'])
            ->with('klient', 'einsatzLeistungsarten.leistungsart')
            ->orderByDesc('datum')
            ->limit(10)
            ->get();
    @endphp
    @if($offeneVergangen->isNotEmpty())
    <div class="karte" style="border-left: 3px solid var(--cs-fehler); margin-top: 1rem;">
        <div class="abschnitt-label" style="color: var(--cs-fehler); margin-bottom: 0.75rem;">
            ⚠ Offene Einsätze — bitte nachbearbeiten
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
            @foreach($offeneVergangen as $e)
            <a href="{{ route('einsaetze.vor-ort', $e) }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; background: #fff5f5; border-radius: var(--cs-radius); font-size: 0.875rem; text-decoration: none; color: inherit; border: 1px solid #fca5a5;">
                <span style="font-size: 0.8rem; color: var(--cs-fehler); min-width: 60px;">{{ $e->datum->format('d.m.') }}</span>
                <span class="text-fett">{{ $e->klient?->vollname() }}</span>
                <span class="text-hell">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</span>
                <span class="badge badge-fehler" style="margin-left: auto; font-size: 0.7rem;">{{ $e->statusLabel() }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    </div>{{-- #panel-touren --}}

    {{-- Nicht eingeplante Einsätze (Lücken) — nur für Admin --}}
    @if($ohneTouren->isNotEmpty() && auth()->user()->rolle !== 'pflege')
    <div class="karte" style="border-left: 3px solid var(--cs-warnung); margin-top: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span class="abschnitt-label" style="margin: 0; color: var(--cs-warnung);">
                    ⚠ Nicht eingeplante Einsätze
                </span>
                <span class="text-hell" style="font-size: 0.8125rem; margin-left: 0.5rem;">
                    ({{ $ohneTouren->flatten()->count() }} Einsatz/Einsätze ohne Tour)
                </span>
            </div>
        </div>

        @foreach($ohneTouren as $benutzerId => $einsaetze)
        @php $ma = $einsaetze->first()->benutzer; @endphp
        <div style="margin-bottom: 0.875rem; padding: 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; font-size: 0.9375rem;">
                    {{ $ma?->vorname }} {{ $ma?->nachname }}
                    <span class="text-hell" style="font-weight: 400; font-size: 0.8125rem; margin-left: 0.25rem;">{{ $einsaetze->count() }} Einsatz{{ $einsaetze->count() !== 1 ? 'ätze' : '' }}</span>
                </span>
                @if(auth()->user()->rolle === 'admin')
                <a href="{{ route('touren.create', ['benutzer_id' => $benutzerId, 'datum' => $datum->format('Y-m-d')]) }}"
                   class="btn btn-primaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">
                    + Tour erstellen
                </a>
                @endif
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                @foreach($einsaetze as $e)
                <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.8125rem; padding: 0.25rem 0; border-top: 1px solid var(--cs-border);">
                    @if($e->zeit_von)
                        <span class="text-hell" style="min-width: 45px;">{{ substr($e->zeit_von,0,5) }}</span>
                    @else
                        <span class="text-hell" style="min-width: 45px;">—</span>
                    @endif
                    <span style="font-weight: 500;">{{ $e->klient?->vollname() }}</span>
                    <span class="text-hell">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

@push('scripts')
<script>
function tourAnsicht(welche) {
    document.getElementById('panel-touren').style.display   = welche === 'touren'   ? 'block' : 'none';
    document.getElementById('panel-klienten').style.display = welche === 'klienten' ? 'block' : 'none';
    document.getElementById('tab-touren').style.borderBottomColor   = welche === 'touren'   ? 'var(--cs-primaer)' : 'transparent';
    document.getElementById('tab-klienten').style.borderBottomColor = welche === 'klienten' ? 'var(--cs-primaer)' : 'transparent';
    document.getElementById('tab-touren').style.color   = welche === 'touren'   ? 'var(--cs-primaer)' : 'var(--cs-text-hell)';
    document.getElementById('tab-klienten').style.color = welche === 'klienten' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)';
}
</script>
@endpush
</x-layouts.app>
