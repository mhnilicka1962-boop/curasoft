<x-layouts.app titel="Chat">

@push('styles')
<style>
    .chat-layout {
        display: flex;
        height: calc(100vh - 80px);
        min-height: 400px;
        border: 1px solid var(--cs-border);
        border-radius: 10px;
        overflow: hidden;
        background: white;
        margin-bottom: -1.5rem;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 240px;
        flex-shrink: 0;
        border-right: 1px solid var(--cs-border);
        display: flex;
        flex-direction: column;
        background: #f9fafb;
    }
    .chat-sidebar-header {
        padding: 0.875rem 1rem;
        font-weight: 700;
        font-size: 0.9375rem;
        border-bottom: 1px solid var(--cs-border);
    }
    .chat-sidebar-section {
        padding: 0.5rem 1rem 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--cs-text-hell);
    }
    .chat-sidebar-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        cursor: pointer;
        font-size: 0.875rem;
        border-left: 3px solid transparent;
        transition: background 0.1s;
    }
    .chat-sidebar-item:hover { background: #f0f4ff; }
    .chat-sidebar-item.aktiv {
        background: #eff6ff;
        border-left-color: var(--cs-primaer);
        font-weight: 600;
        color: var(--cs-primaer);
    }
    .chat-sidebar-item .chat-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--cs-primaer);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .chat-sidebar-item .chat-avatar.team { background: #6366f1; }
    .chat-sidebar-badge {
        margin-left: auto;
        background: #ef4444;
        color: white;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        min-width: 18px;
        text-align: center;
    }
    .chat-neu-btn {
        padding: 0.625rem 0.75rem;
        border-bottom: 1px solid var(--cs-border);
    }
    .chat-neu-btn select {
        width: 100%;
        padding: 0.45rem 0.5rem;
        font-size: 0.8125rem;
        border: 1px solid var(--cs-primaer);
        border-radius: 6px;
        background: #eff6ff;
        cursor: pointer;
        color: var(--cs-primaer);
        font-weight: 600;
    }
    .chat-neu-btn select:focus { outline: 2px solid var(--cs-primaer); }

    /* Panel */
    .chat-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .chat-panel-header {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid var(--cs-border);
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        background: white;
    }
    .chat-panel-titel { font-weight: 700; font-size: 1rem; }
    .chat-autodelete-info {
        font-size: 0.75rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Messages */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .chat-datum-trenner {
        text-align: center;
        font-size: 0.72rem;
        color: #9ca3af;
        margin: 0.75rem 0 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .chat-datum-trenner::before,
    .chat-datum-trenner::after {
        content: ''; flex: 1; height: 1px; background: #e5e7eb;
    }
    .chat-msg-wrap {
        display: flex;
        flex-direction: column;
        width: fit-content;
        max-width: 72%;
    }
    .chat-msg-wrap.ich { align-self: flex-end; align-items: flex-end; }
    .chat-msg-wrap.anderer { align-self: flex-start; align-items: flex-start; }
    .chat-msg-absender {
        font-size: 0.72rem;
        color: #6b7280;
        margin-bottom: 0.15rem;
        padding: 0 0.25rem;
    }
    .chat-bubble {
        padding: 0.4rem 0.65rem;
        border-radius: 14px;
        font-size: 0.8125rem;
        line-height: 1.4;
        word-break: break-word;
        white-space: pre-wrap;
    }
    .chat-bubble.ich {
        background: var(--cs-primaer);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-bubble.anderer {
        background: #f3f4f6;
        color: var(--cs-text);
        border-bottom-left-radius: 4px;
    }
    .chat-bubble-meta {
        display: block;
        font-size: 0.65rem;
        margin-top: 0.25rem;
        opacity: 0.65;
        text-align: right;
    }
    .chat-bubble.anderer .chat-bubble-meta { text-align: left; }
    .chat-msg-meta {
        font-size: 0.68rem;
        color: #9ca3af;
        margin-top: 0.1rem;
        padding: 0 0.25rem;
        display: flex;
        align-items: center;
    }
    .chat-del-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #d1d5db;
        font-size: 0.8rem;
        padding: 0;
        opacity: 0;
        transition: opacity 0.15s, color 0.15s;
        line-height: 1;
    }
    .chat-msg-wrap:hover .chat-del-btn { opacity: 1; }
    .chat-del-btn:hover { color: #ef4444; }

    /* Input */
    .chat-input-area {
        border-top: 1px solid var(--cs-border);
        padding: 0.75rem 1.25rem;
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
        background: white;
    }
    .chat-input-area textarea {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--cs-border);
        border-radius: 20px;
        font-size: 0.875rem;
        resize: none;
        outline: none;
        line-height: 1.4;
        max-height: 120px;
        overflow-y: auto;
        font-family: inherit;
    }
    .chat-input-area textarea:focus { border-color: var(--cs-primaer); }
    .chat-input-area .btn-primaer {
        border-radius: 20px;
        padding: 0.5rem 1.1rem;
        flex-shrink: 0;
    }

    .chat-leer {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 0.875rem;
    }

    @media (max-width: 640px) {
        .chat-layout { height: calc(100vh - 60px); border-radius: 0; margin: -1rem; margin-bottom: -1.5rem; }
        .chat-sidebar { width: 200px; position: absolute; z-index: 10; height: 100%; box-shadow: 2px 0 8px rgba(0,0,0,0.1); }
        .chat-sidebar.versteckt { display: none; }
        .chat-panel { width: 100%; }
        .chat-msg-wrap { max-width: 88%; }
    }
</style>
@endpush

<div class="chat-layout">
    {{-- Sidebar --}}
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">💬 Gespräche</div>

        <div class="chat-neu-btn">
            <form method="POST" id="dm-form">@csrf
                <select id="dm-select" onchange="starteDirekt(this.value)">
                    <option value="">+ Direktnachricht</option>
                    @foreach($benutzer as $b)
                    <option value="{{ $b->id }}">{{ $b->vorname }} {{ $b->nachname }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="chat-sidebar-section">Team</div>
        <div class="chat-sidebar-item" data-chat-id="{{ $teamChat->id }}" data-chat-titel="Team" data-chat-typ="team">
            <div class="chat-avatar team">👥</div>
            <span>Team</span>
            @if($teamUngelesen > 0)
                <span class="chat-sidebar-badge">{{ $teamUngelesen }}</span>
            @endif
        </div>

        @if($direktChats->isNotEmpty())
        <div class="chat-sidebar-section">Direktnachrichten</div>
        <div id="dm-liste">
        @foreach($direktChats as $dc)
        <div class="chat-sidebar-item" data-chat-id="{{ $dc->id }}" data-chat-titel="{{ $dc->anderer_benutzer->vorname }} {{ $dc->anderer_benutzer->nachname }}" data-chat-typ="direkt">
            <div class="chat-avatar">{{ strtoupper(substr($dc->anderer_benutzer->vorname,0,1).substr($dc->anderer_benutzer->nachname,0,1)) }}</div>
            <span>{{ $dc->anderer_benutzer->vorname }} {{ $dc->anderer_benutzer->nachname }}</span>
            @if($dc->ungelesen > 0)
                <span class="chat-sidebar-badge">{{ $dc->ungelesen }}</span>
            @endif
        </div>
        @endforeach
        </div>
        @else
        <div id="dm-liste"></div>
        @endif

    </div>

    {{-- Chat-Panel --}}
    <div class="chat-panel">
        <div class="chat-panel-header">
            <span class="chat-panel-titel" id="chat-titel">Team</span>
            <span id="chat-typ-badge"></span>
            <span class="chat-autodelete-info" style="margin-left:auto;">🗑 14 Tage</span>
        </div>
        <div id="chat-messages" class="chat-messages">
            <div class="chat-leer">Chat auswählen…</div>
        </div>
        <div class="chat-input-area">
            <textarea id="chat-input" placeholder="Nachricht schreiben… (Enter = senden, Shift+Enter = Zeilenumbruch)" rows="1"></textarea>
            <button id="chat-senden" class="btn btn-primaer">Senden</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF        = document.querySelector('meta[name="csrf-token"]').content;
const ICH_ID      = {{ auth()->id() }};
const ICH_TEAM    = {{ auth()->user()->rolle === 'admin' ? 'true' : 'false' }};

let aktiverChatId   = null;
let letzteNachrichtId = 0;
let pollingTimer    = null;
let istTeamChat     = false;

// Initiales Chat aus URL
const urlParams = new URLSearchParams(window.location.search);
const initChat  = urlParams.get('chat');

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar-Klick
    document.querySelectorAll('.chat-sidebar-item').forEach(item => {
        item.addEventListener('click', function () {
            ladeChat(this.dataset.chatId, this.dataset.chatTitel, this.dataset.chatTyp === 'team');
        });
    });

    // Enter-Senden
    document.getElementById('chat-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendeNachricht();
        }
    });
    document.getElementById('chat-senden').addEventListener('click', sendeNachricht);

    // Auto-Resize Textarea
    document.getElementById('chat-input').addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Init-Chat aus URL oder Team-Chat
    if (initChat) {
        const item = document.querySelector(`[data-chat-id="${initChat}"]`);
        if (item) item.click();
        else ladeChat({{ $teamChat->id }}, 'Team', true);
    } else {
        document.querySelector(`[data-chat-id="{{ $teamChat->id }}"]`).click();
    }

    // Sidebar alle 10 Sek. aktualisieren (neue DMs erkennen)
    setInterval(aktualisiereSidebar, 10000);
});

function ladeChat(chatId, titel, isTeam) {
    aktiverChatId     = chatId;
    istTeamChat       = isTeam;
    letzteNachrichtId = 0;

    // Sidebar aktiv setzen
    document.querySelectorAll('.chat-sidebar-item').forEach(i => i.classList.remove('aktiv'));
    const item = document.querySelector(`[data-chat-id="${chatId}"]`);
    if (item) item.classList.add('aktiv');

    document.getElementById('chat-titel').textContent = titel;
    const badge = document.getElementById('chat-typ-badge');
    badge.innerHTML = isTeam
        ? '<span class="badge badge-info">👥 An alle</span>'
        : '<span class="badge badge-warnung">💬 Persönlich</span>';
    document.getElementById('chat-messages').innerHTML = '<div class="chat-leer">Lädt…</div>';

    // URL aktualisieren
    history.replaceState(null, '', `/chat?chat=${chatId}`);

    // Alle Nachrichten laden
    fetch(`/chat/${chatId}/nachrichten`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json())
        .then(msgs => {
            zeigeNachrichten(msgs, true);
            scrollNachUnten();
            startePolling();
            if (msgs.length) markiereGesehen(chatId, msgs[msgs.length-1].id);
        });
}

function startePolling() {
    clearInterval(pollingTimer);
    pollingTimer = setInterval(polleNeu, 5000);
}

function polleNeu() {
    if (!aktiverChatId) return;
    fetch(`/chat/${aktiverChatId}/nachrichten?seit=${letzteNachrichtId}`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json())
        .then(msgs => {
            if (msgs.length > 0) {
                const container = document.getElementById('chat-messages');
                const amUnten   = container.scrollHeight - container.scrollTop - container.clientHeight < 60;
                zeigeNachrichten(msgs, false);
                if (amUnten) scrollNachUnten();
                markiereGesehen(aktiverChatId, msgs[msgs.length-1].id);
            }
        });
}

function zeigeNachrichten(msgs, ersetzen) {
    const container = document.getElementById('chat-messages');
    if (ersetzen) container.innerHTML = '';

    if (ersetzen && msgs.length === 0) {
        container.innerHTML = '<div class="chat-leer">Noch keine Nachrichten. Schreib etwas! 👋</div>';
        return;
    }

    // "Leer"-Hinweis entfernen sobald Nachrichten kommen
    const leerDiv = container.querySelector('.chat-leer');
    if (leerDiv && msgs.length > 0) leerDiv.remove();

    msgs.forEach(msg => {
        if (msg.id > letzteNachrichtId) letzteNachrichtId = msg.id;

        // Lokales Datum aus Timestamp
        const d0    = new Date(msg.ts * 1000);
        const lokal = String(d0.getDate()).padStart(2,'0') + '.' + String(d0.getMonth()+1).padStart(2,'0') + '.' + d0.getFullYear();

        // Datum-Trenner prüfen
        const letztes = container.lastElementChild;
        const letztesDatum = letztes?.dataset?.datum;
        if (letztesDatum !== lokal) {
            const trenner = document.createElement('div');
            trenner.className = 'chat-datum-trenner';
            trenner.textContent = lokal === heuteDatum() ? 'Heute' : lokal;
            container.appendChild(trenner);
        }

        const wrap = document.createElement('div');
        wrap.className = `chat-msg-wrap ${msg.ich ? 'ich' : 'anderer'}`;
        wrap.dataset.datum  = lokal;
        wrap.dataset.msgId  = msg.id;

        const name     = msg.ich ? 'Du' : msg.absender_name;
        const loeschBtn = msg.kannLoeschen
            ? `<button class="chat-del-btn" onclick="loescheNachricht(${msg.id}, ${aktiverChatId})" title="Löschen">✕</button>`
            : '';

        const zeit  = String(d0.getHours()).padStart(2,'0') + ':' + String(d0.getMinutes()).padStart(2,'0');

        wrap.innerHTML = `
            <div class="chat-msg-absender">${name}</div>
            <div class="chat-bubble ${msg.ich ? 'ich' : 'anderer'}">${msg.inhalt}<span class="chat-bubble-meta">${lokal}, ${zeit}</span></div>
            ${loeschBtn ? `<div class="chat-msg-meta">${loeschBtn}</div>` : ''}`;

        container.appendChild(wrap);
    });
}

function scrollNachUnten() {
    const c = document.getElementById('chat-messages');
    c.scrollTop = c.scrollHeight;
}

function sendeNachricht() {
    const input  = document.getElementById('chat-input');
    const inhalt = input.value.trim();
    if (!inhalt || !aktiverChatId) return;

    input.value = '';
    input.style.height = 'auto';

    fetch(`/chat/${aktiverChatId}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ inhalt }),
    })
    .then(r => r.json())
    .then(msg => {
        // Leer-Hinweis entfernen
        const leer = document.querySelector('.chat-leer');
        if (leer) leer.remove();

        zeigeNachrichten([msg], false);
        scrollNachUnten();
    });
}

function loescheNachricht(nachrichtId, chatId) {
    if (!confirm('Nachricht löschen?')) return;
    fetch(`/chat/${chatId}/nachrichten/${nachrichtId}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(() => {
        const wrap = document.querySelector(`[data-msg-id="${nachrichtId}"]`);
        if (wrap) wrap.remove();
    });
}

function aktualisiereSidebar() {
    fetch('/chat/sidebar', { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json())
        .then(chats => {
            const dmBereich = document.getElementById('dm-liste');
            chats.forEach(chat => {
                let item = document.querySelector(`[data-chat-id="${chat.id}"]`);
                if (!item) {
                    // Neuer DM-Chat — zur Sidebar hinzufügen
                    if (!dmBereich) {
                        const section = document.createElement('div');
                        section.className = 'chat-sidebar-section';
                        section.textContent = 'Direktnachrichten';
                        document.querySelector('.chat-sidebar-item').parentNode
                            .insertBefore(section, document.querySelector('.chat-sidebar-item').nextSibling);
                        const liste = document.createElement('div');
                        liste.id = 'dm-liste';
                        section.after(liste);
                    }
                    item = document.createElement('div');
                    item.className = 'chat-sidebar-item';
                    item.dataset.chatId    = chat.id;
                    item.dataset.chatTitel = chat.name;
                    item.dataset.chatTyp   = 'direkt';
                    item.innerHTML = `<div class="chat-avatar">${chat.initials}</div><span>${chat.name}</span><span class="dm-badge" style="display:none;background:#ef4444;color:white;border-radius:10px;font-size:0.65rem;padding:0 5px;margin-left:auto;">Neu</span>`;
                    item.addEventListener('click', function() {
                        ladeChat(this.dataset.chatId, this.dataset.chatTitel, false);
                    });
                    (document.getElementById('dm-liste') || document.querySelector('.chat-sidebar')).appendChild(item);
                }

                // Ungelesen-Badge
                const gesehenId = parseInt(localStorage.getItem('chat_gesehen_' + chat.id) || '0');
                const badge = item.querySelector('.dm-badge');
                if (badge) {
                    badge.style.display = (chat.letzteId > gesehenId && aktiverChatId != chat.id) ? 'inline' : 'none';
                }
            });
        });
}

function markiereGesehen(chatId, letzteId) {
    localStorage.setItem('chat_gesehen_' + chatId, letzteId);
    const badge = document.querySelector(`[data-chat-id="${chatId}"] .dm-badge`);
    if (badge) badge.style.display = 'none';
}

function heuteDatum() {
    const h = new Date();
    return String(h.getDate()).padStart(2,'0') + '.' + String(h.getMonth()+1).padStart(2,'0') + '.' + h.getFullYear();
}

function starteDirekt(benutzerId) {
    if (!benutzerId) return;
    document.getElementById('dm-select').value = '';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/chat/direkt/${benutzerId}`;
    form.innerHTML = `<input type="hidden" name="_token" value="${CSRF}">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush

</x-layouts.app>
