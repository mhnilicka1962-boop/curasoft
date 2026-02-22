<x-layouts.app :titel="'Rapport: ' . $rapport->klient->vollname()">
<div style="max-width: 800px;">

    <a href="{{ route('rapporte.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Rapporte</a>

    <div class="seiten-kopf">
        <div>
            <h1 style="font-size: 1.125rem; font-weight: 700; margin: 0;">
                {{ \App\Models\Rapport::$typen[$rapport->rapport_typ] ?? $rapport->rapport_typ }}
            </h1>
            <div class="text-klein text-hell" style="margin-top: 0.25rem;">
                <a href="{{ route('klienten.show', $rapport->klient) }}" class="link-primaer">{{ $rapport->klient->vollname() }}</a>
                · {{ $rapport->datum->format('d.m.Y') }}
                @if($rapport->zeit_von) · {{ $rapport->zeit_von }}@if($rapport->zeit_bis) – {{ $rapport->zeit_bis }}@endif @endif
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if($rapport->vertraulich)
                <span class="badge badge-warnung">Vertraulich</span>
            @endif
        </div>
    </div>

    <div class="karte">
        <div style="font-size: 0.875rem; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->inhalt }}</div>

        <div class="abschnitt-trenn text-klein text-hell" style="margin-top: 1.25rem; padding-top: 1rem; display: flex; gap: 1.5rem; flex-wrap: wrap;">
            @if($rapport->benutzer)
                <span>Verfasst von: <strong style="color: var(--cs-text);">{{ $rapport->benutzer->vorname }} {{ $rapport->benutzer->nachname }}</strong></span>
            @endif
            @if($rapport->einsatz)
                <span>Einsatz: <a href="{{ route('einsaetze.show', $rapport->einsatz) }}" class="link-primaer">{{ $rapport->einsatz->datum->format('d.m.Y') }}</a></span>
            @endif
            <span>Erstellt: {{ $rapport->created_at->format('d.m.Y H:i') }}</span>
        </div>
    </div>

</div>
</x-layouts.app>
