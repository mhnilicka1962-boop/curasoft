<x-layouts.app :titel="$nachricht->betreff">
<div style="max-width: 700px;">

    <a href="{{ route('nachrichten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Posteingang</a>

    {{-- Thread-Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;">
        <h2 style="font-size: 1.125rem; font-weight: 700; margin: 0;">{{ $nachricht->betreff }}</h2>
        <div style="display: flex; gap: 0.5rem;">
            @if($istEmpfaenger)
            <form method="POST" action="{{ route('nachrichten.archivieren', $nachricht) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Archivieren</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Originalnachricht --}}
    <div class="karte" style="margin-bottom: 0.75rem;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.75rem; gap: 1rem; flex-wrap: wrap;">
            <div class="text-hell" style="font-size: 0.8125rem; display: flex; flex-wrap: wrap; gap: 0.375rem 1rem;">
                <span>Von: <strong style="color: var(--cs-text);">{{ $nachricht->absender->name }}</strong></span>
                <span>An:
                    @foreach($nachricht->empfaenger as $e)
                        <strong style="color: var(--cs-text);">{{ $e->empfaenger?->name }}</strong>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </span>
                <span>{{ $nachricht->created_at->format('d.m.Y, H:i') }} Uhr</span>
            </div>
        </div>
        <hr style="border: none; border-top: 1px solid var(--cs-border); margin: 0 0 0.875rem;">
        <div style="font-size: 0.9375rem; line-height: 1.7; white-space: pre-wrap; color: var(--cs-text);">{{ $nachricht->inhalt }}</div>
    </div>

    {{-- Antworten im Thread --}}
    @foreach($nachricht->antworten as $antwort)
    <div class="karte" style="margin-bottom: 0.75rem; border-left: 3px solid var(--cs-primaer); padding-left: calc(1.25rem - 3px);">
        <div class="text-hell" style="font-size: 0.8125rem; display: flex; flex-wrap: wrap; gap: 0.375rem 1rem; margin-bottom: 0.625rem;">
            <span>Von: <strong style="color: var(--cs-text);">{{ $antwort->absender->name }}</strong></span>
            <span>{{ $antwort->created_at->format('d.m.Y, H:i') }} Uhr</span>
        </div>
        <hr style="border: none; border-top: 1px solid var(--cs-border); margin: 0 0 0.75rem;">
        <div style="font-size: 0.9375rem; line-height: 1.7; white-space: pre-wrap; color: var(--cs-text);">{{ $antwort->inhalt }}</div>
    </div>
    @endforeach

    {{-- Antwort-Formular --}}
    <div class="karte" style="margin-top: 0.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Antworten</div>
        <form method="POST" action="{{ route('nachrichten.antworten', $nachricht) }}">
            @csrf
            <textarea name="inhalt" class="feld" rows="4" required
                style="resize: vertical; margin-bottom: 0.875rem;"
                placeholder="Antwort eingeben…">{{ old('inhalt') }}</textarea>
            @error('inhalt') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-bottom: 0.5rem;">{{ $message }}</div> @enderror
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Antwort senden</button>
                <a href="{{ route('nachrichten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>

</div>
</x-layouts.app>
