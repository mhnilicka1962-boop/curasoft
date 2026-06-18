<x-layout titel="Bedarfsanalyse — Schritt {{ $schritt }} von 5">

@push('styles')
<style>
.ba-fortschritt {
    display: flex;
    gap: 0;
    margin-bottom: 2rem;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--cs-rand);
}
.ba-schritt {
    flex: 1;
    padding: 0.6rem 0.5rem;
    text-align: center;
    font-size: 0.78rem;
    font-weight: 500;
    background: var(--cs-hintergrund);
    color: var(--cs-text-gedaempft);
    border-right: 1px solid var(--cs-rand);
    cursor: default;
    transition: background 0.15s;
}
.ba-schritt:last-child { border-right: none; }
.ba-schritt.aktiv {
    background: var(--cs-primaer);
    color: white;
    font-weight: 700;
}
.ba-schritt.erledigt {
    background: var(--cs-primaer-hell, #dbeafe);
    color: var(--cs-primaer);
    cursor: pointer;
}
.ba-schritt a { color: inherit; text-decoration: none; display: block; }
.ba-schritt-nr { display: block; font-size: 1rem; font-weight: 700; }

.ba-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 640px) {
    .ba-form-grid { grid-template-columns: 1fr; }
    .ba-schritt-titel { display: none; }
}
.ba-sektion {
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    background: var(--cs-hintergrund);
    border: 1px solid var(--cs-rand);
    border-radius: 8px;
}
.ba-sektion h3 {
    margin: 0 0 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--cs-text-gedaempft);
    font-weight: 600;
}
.ba-voll { grid-column: 1 / -1; }
.ba-radio-gruppe { display: flex; flex-direction: column; gap: 0.5rem; }
.ba-radio-option {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.6rem 0.75rem;
    border: 1px solid var(--cs-rand);
    border-radius: 6px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.ba-radio-option:has(input:checked) {
    border-color: var(--cs-primaer);
    background: var(--cs-primaer-hell, #dbeafe);
}
.ba-radio-option input { margin-top: 2px; flex-shrink: 0; }
.ba-radio-label { font-size: 0.875rem; line-height: 1.4; }
.ba-radio-label strong { display: block; margin-bottom: 0.15rem; }
.ba-info-box {
    background: #fef9c3;
    border: 1px solid #fde047;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #713f12;
    grid-column: 1 / -1;
}
.ba-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--cs-rand);
}
#ba-speichern-status {
    font-size: 0.8rem;
    color: var(--cs-text-gedaempft);
    transition: opacity 0.3s;
}
</style>
@endpush

<div class="seiten-kopf">
    <div>
        <a href="{{ route('bedarfsanalysen.index') }}" class="btn btn-sekundaer" style="margin-bottom:0.5rem;">← Übersicht</a>
        <h1>Bedarfsanalyse — {{ $analyse->anzeigeName() }}</h1>
    </div>
</div>

{{-- Fortschrittsanzeige --}}
<div class="ba-fortschritt">
    @for($i = 1; $i <= 5; $i++)
    @php
        $titel = $analyse->schrittTitel($i);
        $klasse = $i === $schritt ? 'aktiv' : ($i <= $analyse->aktueller_schritt ? 'erledigt' : '');
    @endphp
    <div class="ba-schritt {{ $klasse }}">
        @if($klasse === 'erledigt')
            <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => $i]) }}">
                <span class="ba-schritt-nr">{{ $i }}</span>
                <span class="ba-schritt-titel">{{ $titel }}</span>
            </a>
        @else
            <span class="ba-schritt-nr">{{ $i }}</span>
            <span class="ba-schritt-titel">{{ $titel }}</span>
        @endif
    </div>
    @endfor
</div>

<form id="ba-form"
      action="{{ route('bedarfsanalysen.schritt.speichern', ['analyse' => $analyse->id, 'schritt' => $schritt]) }}"
      method="POST">
    @csrf

    @include('bedarfsanalysen.partials.schritt-' . $schritt, ['analyse' => $analyse])

    <div class="ba-nav">
        <div>
            @if($schritt > 1)
            <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => $schritt - 1]) }}"
               class="btn btn-sekundaer">← Zurück</a>
            @endif
        </div>
        <div style="display:flex; align-items:center; gap:1rem;">
            <span id="ba-speichern-status"></span>
            @if($schritt < 5)
            <button type="submit" class="btn btn-primaer">Weiter →</button>
            @else
            <button type="submit" class="btn btn-primaer">Weiter zur Zusammenfassung →</button>
            @endif
        </div>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('ba-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form   = this;
    const status = document.getElementById('ba-speichern-status');
    const data   = new FormData(form);

    status.textContent = 'Wird gespeichert…';

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: data,
    })
    .then(r => r.json())
    .then(json => {
        if (json.ok) {
            status.textContent = '✓ Gespeichert';
            setTimeout(() => {
                // Navigate to next step or summary
                const nextUrl = form.dataset.nextUrl;
                if (nextUrl) window.location.href = nextUrl;
                else form.submit();
            }, 300);
        }
    })
    .catch(() => { form.submit(); });
});
</script>
@endpush

{{-- Set next URL for JS navigation --}}
<script>
document.getElementById('ba-form').dataset.nextUrl =
    @if($schritt < 5)
        "{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => $schritt + 1]) }}";
    @else
        "{{ route('bedarfsanalysen.abschliessen.form', $analyse) }}";
    @endif
</script>

</x-layout>
