<x-layouts.app :titel="$nachricht->betreff">
<div style="max-width: 700px;">

    <a href="{{ route('nachrichten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Posteingang</a>

    {{-- Nachricht --}}
    <div class="karte" style="margin-bottom: 1rem;">
        {{-- Header --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 1.125rem; font-weight: 700; margin: 0 0 0.375rem;">{{ $nachricht->betreff }}</h2>
                <div class="text-hell" style="font-size: 0.8125rem; display: flex; flex-wrap: wrap; gap: 0.5rem 1rem;">
                    <span>Von: <strong style="color: var(--cs-text);">{{ $nachricht->absender->name }}</strong></span>
                    <span>An:
                        @foreach($nachricht->empfaenger as $e)
                            <strong style="color: var(--cs-text);">{{ $e->empfaenger?->name }}</strong>{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </span>
                    <span>{{ $nachricht->created_at->format('d.m.Y, H:i') }} Uhr</span>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                @if($istEmpfaenger)
                <form method="POST" action="{{ route('nachrichten.archivieren', $nachricht) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Archivieren</button>
                </form>
                @endif
                <a href="{{ route('nachrichten.create', ['betreff' => 'Re: ' . $nachricht->betreff, 'empfaenger' => [$nachricht->absender_id]]) }}"
                    class="btn btn-sekundaer" style="font-size: 0.8125rem;">Weiterleiten</a>
            </div>
        </div>

        {{-- Trennlinie --}}
        <hr style="border: none; border-top: 1px solid var(--cs-border); margin: 0 0 1rem;">

        {{-- Nachrichtentext --}}
        <div style="font-size: 0.9375rem; line-height: 1.7; white-space: pre-wrap; color: var(--cs-text);">{{ $nachricht->inhalt }}</div>
    </div>

    {{-- Antwort-Formular --}}
    @if($istEmpfaenger && $nachricht->absender_id !== auth()->id())
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">
            Antworten an {{ $nachricht->absender->vorname }} {{ $nachricht->absender->nachname }}
        </div>
        <form method="POST" action="{{ route('nachrichten.antworten', $nachricht) }}">
            @csrf
            <textarea name="inhalt" class="feld" rows="5" required
                style="resize: vertical; margin-bottom: 0.875rem;"
                placeholder="Antwort eingeben…">{{ old('inhalt') }}</textarea>
            @error('inhalt') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-bottom: 0.5rem;">{{ $message }}</div> @enderror
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Antwort senden</button>
                <a href="{{ route('nachrichten.create', ['empfaenger' => [$nachricht->absender_id]]) }}" class="btn btn-sekundaer">Neue Nachricht</a>
            </div>
        </form>
    </div>
    @endif

</div>
</x-layouts.app>
