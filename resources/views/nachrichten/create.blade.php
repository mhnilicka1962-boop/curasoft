<x-layouts.app :titel="'Neue Nachricht'">
<div style="max-width: 700px;">
    <a href="{{ route('nachrichten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Posteingang</a>

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Neue Nachricht</div>

        <form method="POST" action="{{ route('nachrichten.store') }}">
            @csrf

            {{-- Empfänger --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Empfänger <span style="color:var(--cs-fehler);">*</span></label>
                <div style="border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.375rem; min-height: 2.5rem;" id="empfaenger-container">
                    @foreach($benutzer as $b)
                    <label style="display: flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 999px; cursor: pointer; font-size: 0.8125rem; border: 1px solid var(--cs-border); background: var(--cs-hintergrund); transition: all 0.15s;"
                        id="label-{{ $b->id }}">
                        <input type="checkbox" name="empfaenger_ids[]" value="{{ $b->id }}"
                            {{ in_array($b->id, (array)$empfaengerIds) ? 'checked' : '' }}
                            onchange="toggleEmpfaenger({{ $b->id }})"
                            style="display: none;">
                        <span style="width: 1.5rem; height: 1.5rem; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700;">
                            {{ strtoupper(substr($b->vorname, 0, 1) . substr($b->nachname, 0, 1)) }}
                        </span>
                        {{ $b->vorname }} {{ $b->nachname }}
                        <span class="text-mini text-hell">({{ $b->rolle }})</span>
                    </label>
                    @endforeach
                </div>
                @error('empfaenger_ids') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror

                {{-- Alle auswählen --}}
                <div style="margin-top: 0.375rem;">
                    <button type="button" onclick="alleAuswaehlen(true)" class="btn btn-sekundaer" style="padding: 0.2rem 0.625rem; font-size: 0.75rem;">Alle</button>
                    <button type="button" onclick="alleAuswaehlen(false)" class="btn btn-sekundaer" style="padding: 0.2rem 0.625rem; font-size: 0.75rem;">Keine</button>
                </div>
            </div>

            {{-- Betreff --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Betreff <span style="color:var(--cs-fehler);">*</span></label>
                <input type="text" name="betreff" class="feld" value="{{ old('betreff', $betreff) }}" required maxlength="200" placeholder="Betreff der Nachricht">
                @error('betreff') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            {{-- Nachricht --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Nachricht <span style="color:var(--cs-fehler);">*</span></label>
                <textarea name="inhalt" class="feld" rows="8" required maxlength="10000"
                    style="resize: vertical;"
                    placeholder="Nachricht eingeben…">{{ old('inhalt') }}</textarea>
                @error('inhalt') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Senden</button>
                <a href="{{ route('nachrichten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleEmpfaenger(id) {
    const checkbox = document.querySelector(`input[value="${id}"]`);
    const label = document.getElementById(`label-${id}`);
    if (checkbox.checked) {
        label.style.background = 'var(--cs-primaer)';
        label.style.color = '#fff';
        label.style.borderColor = 'var(--cs-primaer)';
    } else {
        label.style.background = 'var(--cs-hintergrund)';
        label.style.color = '';
        label.style.borderColor = 'var(--cs-border)';
    }
}

function alleAuswaehlen(an) {
    document.querySelectorAll('input[name="empfaenger_ids[]"]').forEach(cb => {
        cb.checked = an;
        toggleEmpfaenger(cb.value);
    });
}

// Initial state
document.querySelectorAll('input[name="empfaenger_ids[]"]:checked').forEach(cb => toggleEmpfaenger(cb.value));
</script>
@endpush
</x-layouts.app>
