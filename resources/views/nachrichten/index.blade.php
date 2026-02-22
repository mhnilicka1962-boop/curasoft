<x-layouts.app :titel="'Nachrichten'">

<div class="seiten-kopf">
    <div>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">
            Nachrichten
            @if($ungelesen > 0)
                <span style="display: inline-flex; align-items: center; justify-content: center; background: var(--cs-primaer); color: #fff; border-radius: 999px; font-size: 0.75rem; font-weight: 700; min-width: 1.4rem; height: 1.4rem; padding: 0 0.35rem; margin-left: 0.4rem;">{{ $ungelesen }}</span>
            @endif
        </h1>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('nachrichten.create') }}" class="btn btn-primaer">+ Neue Nachricht</a>
    </div>
</div>

{{-- Tabs --}}
<div style="display: flex; gap: 0; border-bottom: 2px solid var(--cs-border); margin-bottom: 1.25rem;">
    <a href="{{ route('nachrichten.index', ['tab' => 'posteingang']) }}"
        style="padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; color: {{ $tab === 'posteingang' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; border-bottom-color: {{ $tab === 'posteingang' ? 'var(--cs-primaer)' : 'transparent' }};">
        Posteingang
        @if($ungelesen > 0 && $tab !== 'posteingang')
            <span style="background: var(--cs-primaer); color: #fff; border-radius: 999px; font-size: 0.7rem; padding: 0.1rem 0.4rem; margin-left: 0.25rem;">{{ $ungelesen }}</span>
        @endif
    </a>
    <a href="{{ route('nachrichten.index', ['tab' => 'gesendet']) }}"
        style="padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; color: {{ $tab === 'gesendet' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; border-bottom-color: {{ $tab === 'gesendet' ? 'var(--cs-primaer)' : 'transparent' }};">
        Gesendet
    </a>
</div>

{{-- Posteingang --}}
@if($tab === 'posteingang')
<div class="karte-null">
    @forelse($posteingang as $eintrag)
    @php $ungelesen_msg = $eintrag->istUngelesen(); @endphp
    <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border); background: {{ $ungelesen_msg ? 'var(--cs-primaer-hell)' : 'transparent' }}; transition: background 0.15s;">
        {{-- Absender-Avatar --}}
        <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; flex-shrink: 0;">
            {{ strtoupper(substr($eintrag->nachricht->absender->vorname ?? '?', 0, 1)) }}{{ strtoupper(substr($eintrag->nachricht->absender->nachname ?? '', 0, 1)) }}
        </div>

        {{-- Inhalt --}}
        <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem;">
                <div style="font-weight: {{ $ungelesen_msg ? '700' : '500' }}; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $eintrag->nachricht->absender->name ?? 'Unbekannt' }}
                </div>
                <div class="text-mini text-hell" style="flex-shrink: 0;">
                    {{ $eintrag->nachricht->created_at->diffForHumans() }}
                </div>
            </div>
            <a href="{{ route('nachrichten.show', $eintrag->nachricht) }}" style="text-decoration: none; color: inherit;">
                <div style="font-weight: {{ $ungelesen_msg ? '600' : '400' }}; font-size: 0.875rem; color: var(--cs-text);">
                    {{ $eintrag->nachricht->betreff }}
                </div>
                <div class="text-hell" style="font-size: 0.8125rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60ch;">
                    {{ Str::limit(strip_tags($eintrag->nachricht->inhalt), 100) }}
                </div>
            </a>
        </div>

        {{-- Aktionen --}}
        <div style="flex-shrink: 0; display: flex; gap: 0.375rem;">
            @if($ungelesen_msg)
                <span class="badge badge-info" style="font-size: 0.7rem;">Neu</span>
            @endif
            <form method="POST" action="{{ route('nachrichten.archivieren', $eintrag->nachricht) }}">
                @csrf @method('PATCH')
                <button type="submit" title="Archivieren" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✕</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-mitte text-hell" style="padding: 3rem;">
        Posteingang ist leer.
    </div>
    @endforelse
</div>

@if($posteingang->hasPages())
<div style="margin-top: 1rem;">{{ $posteingang->appends(['tab' => 'posteingang'])->links() }}</div>
@endif

{{-- Gesendet --}}
@else
<div class="karte-null">
    @forelse($gesendet as $nachricht)
    <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);">
        <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: var(--cs-text-hell); flex-shrink: 0;">
            →
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem;">
                <div class="text-hell" style="font-size: 0.8125rem;">
                    An:
                    @foreach($nachricht->empfaenger->take(3) as $e)
                        {{ $e->empfaenger?->vorname }} {{ $e->empfaenger?->nachname }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                    @if($nachricht->empfaenger->count() > 3)
                        + {{ $nachricht->empfaenger->count() - 3 }} weitere
                    @endif
                </div>
                <div class="text-mini text-hell" style="flex-shrink: 0;">
                    {{ $nachricht->created_at->diffForHumans() }}
                </div>
            </div>
            <a href="{{ route('nachrichten.show', $nachricht) }}" style="text-decoration: none; color: inherit;">
                <div style="font-size: 0.875rem; font-weight: 500;">{{ $nachricht->betreff }}</div>
                <div class="text-hell" style="font-size: 0.8125rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ Str::limit(strip_tags($nachricht->inhalt), 100) }}
                </div>
            </a>
        </div>
        <div style="flex-shrink: 0;">
            @php $gelesen = $nachricht->empfaenger->whereNotNull('gelesen_am')->count(); @endphp
            @php $total = $nachricht->empfaenger->count(); @endphp
            <span style="font-size: 0.75rem; color: {{ $gelesen === $total ? 'var(--cs-erfolg)' : 'var(--cs-text-hell)' }};">
                {{ $gelesen }}/{{ $total }} gelesen
            </span>
        </div>
    </div>
    @empty
    <div class="text-mitte text-hell" style="padding: 3rem;">
        Noch keine Nachrichten gesendet.
    </div>
    @endforelse
</div>

@if($gesendet->hasPages())
<div style="margin-top: 1rem;">{{ $gesendet->appends(['tab' => 'gesendet'])->links() }}</div>
@endif
@endif

</x-layouts.app>
