import { Calendar }           from '@fullcalendar/core';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import interactionPlugin      from '@fullcalendar/interaction';
import deLocale               from '@fullcalendar/core/locales/de';

// Globale Referenz für Blade-View
window.KalenderInit = function(mitarbeiter, klienten, horizont) {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const ressourcenAngestellte = [
        { id: 'unzugeteilt', title: '— Nicht zugeteilt —' },
        ...mitarbeiter.map(m => ({ id: String(m.id), title: m.vorname + ' ' + m.nachname }))
    ];

    const ressourcenKlienten = klienten.map(k => ({ id: 'k' + String(k.id), title: k.vorname + ' ' + k.nachname, klient_id: k.id }));

    let ansicht = 'angestellte'; // 'angestellte' | 'klienten'

    const kalender = new Calendar(document.getElementById('kalender'), {
        plugins:             [resourceTimelinePlugin, interactionPlugin],
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView:         'resourceTimelineWeek',
        locale:              deLocale,
        firstDay:            1,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'resourceTimelineDay,resourceTimelineWeek,resourceTimeline2Wochen,resourceTimelineMonth',
        },
        buttonText: {
            prev:                    '←',
            next:                    '→',
            today:                   'Heute',
            resourceTimelineDay:      'Tag',
            resourceTimelineWeek:     'Woche',
            resourceTimeline2Wochen:  '2 Wo.',
            resourceTimelineMonth:    'Monat',
        },
        resourceAreaHeaderContent: 'Angestellte',
        resources:  ressourcenAngestellte,
        eventSources: [
            '/kalender/einsaetze',
            {
                events: function(info, successCallback) {
                    if (!horizont) { successCallback([]); return; }
                    // Tag nach Horizont bis 2 Jahre voraus hellrot markieren
                    const horizonDate = new Date(horizont);
                    horizonDate.setDate(horizonDate.getDate() + 1);
                    const bis = new Date(horizonDate);
                    bis.setFullYear(bis.getFullYear() + 2);
                    successCallback([{
                        start:   horizonDate.toISOString().slice(0, 10),
                        end:     bis.toISOString().slice(0, 10),
                        display: 'background',
                        color:   '#fee2e2',
                    }]);
                }
            }
        ],
        editable:   true,
        droppable:  true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        resourceAreaWidth: '150px',
        height: '100%',
        slotDuration: '01:00:00',
        slotLabelInterval: '01:00:00',
        expandRows: false,
        views: {
            resourceTimelineDay: {
                slotMinWidth: 48,
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false, omitZeroMinute: false },
            },
            resourceTimelineWeek: {
                slotMinWidth: 34,
                slotLabelFormat: [
                    { weekday: 'short', day: 'numeric', month: 'numeric' },
                    { hour: '2-digit', minute: '2-digit', hour12: false, omitZeroMinute: false },
                ],
            },
            resourceTimeline2Wochen: {
                type:     'resourceTimeline',
                duration: { weeks: 2 },
                slotMinWidth: 22,
                slotDuration: '01:00:00',
                slotLabelFormat: [
                    { weekday: 'short', day: 'numeric', month: 'numeric' },
                    { hour: '2-digit', minute: '2-digit', hour12: false, omitZeroMinute: false },
                ],
            },
            resourceTimelineMonth: {
                slotMinWidth: 28,
                slotDuration: '01:00:00',
                slotLabelFormat: [
                    { weekday: 'short', day: 'numeric' },
                    { hour: '2-digit', minute: '2-digit', hour12: false, omitZeroMinute: false },
                ],
            },
        },

        eventContent: function(arg) {
            const p    = arg.event.extendedProps;
            const text = ansicht === 'klienten'
                ? (p.benutzer_name ?? '—') + (p.zeit_von ? ' ' + p.zeit_von : '')
                : arg.event.title;
            const div = document.createElement('div');
            div.className = 'fc-event-title fc-sticky';
            div.textContent = text;
            return { domNodes: [div] };
        },

        eventClick: function(info) {
            zeigePopup(info.event, info.jsEvent);
        },

        dateClick: function(info) {
            const jetzt = Date.now();
            if (kalender._letzterKlick && jetzt - kalender._letzterKlick < 400
                && kalender._letzterKlickRes === info.resource?.id
                && kalender._letzterKlickDate === info.dateStr) {
                const resId   = info.resource?.id;
                const datum   = info.dateStr.split('T')[0];
                const zeitVon = info.dateStr.includes('T') ? info.dateStr.split('T')[1].slice(0, 5) : '';
                let url = `/einsaetze/create?datum=${datum}`;
                if (zeitVon) url += `&zeit_von=${zeitVon}`;
                if (ansicht === 'angestellte' && resId && resId !== 'unzugeteilt') {
                    url += `&benutzer_id=${resId}`;
                } else if (ansicht === 'klienten' && resId && resId.startsWith('k')) {
                    url += `&klient_id=${resId.slice(1)}`;
                }
                window.location.href = url;
            }
            kalender._letzterKlick     = jetzt;
            kalender._letzterKlickRes  = info.resource?.id;
            kalender._letzterKlickDate = info.dateStr;
        },

        eventDrop: function(info) {
            if (ansicht !== 'angestellte') { info.revert(); return; }
            const e      = info.event;
            const neueRes = info.newResource;

            const data = {
                datum:       e.startStr.split('T')[0],
                zeit_von:    e.allDay ? null : e.startStr.split('T')[1]?.slice(0, 5),
                zeit_bis:    e.allDay ? null : e.endStr?.split('T')[1]?.slice(0, 5),
                benutzer_id: neueRes
                    ? (neueRes.id === 'unzugeteilt' ? null : neueRes.id)
                    : undefined,
            };

            fetch(`/kalender/einsaetze/${e.id}`, {
                method:  'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify(data),
            })
            .then(r => r.json())
            .then(json => {
                if (json.fehler) { alert(json.fehler); info.revert(); }
            })
            .catch(() => info.revert());
        },
    });

    kalender.render();

    // Ansicht-Toggle
    document.getElementById('kl-ansicht-toggle').addEventListener('click', function() {
        ansicht = ansicht === 'angestellte' ? 'klienten' : 'angestellte';
        if (ansicht === 'klienten') {
            kalender.setOption('resources', ressourcenKlienten);
            kalender.setOption('resourceAreaHeaderContent', 'Klient');
            kalender.setOption('editable', false);
            this.textContent = 'Ansicht: Klienten';
        } else {
            kalender.setOption('resources', ressourcenAngestellte);
            kalender.setOption('resourceAreaHeaderContent', 'Angestellte');
            kalender.setOption('editable', true);
            this.textContent = 'Ansicht: Angestellte';
        }
        kalender.refetchEvents();
    });

    // Zeitbereich-Controls
    document.getElementById('kl-von').addEventListener('change', function() {
        kalender.setOption('slotMinTime', this.value);
    });
    document.getElementById('kl-bis').addEventListener('change', function() {
        kalender.setOption('slotMaxTime', this.value);
    });

    function zeigePopup(event, jsEvent) {
        const p     = event.extendedProps;
        const popup = document.getElementById('kl-popup');

        document.getElementById('kl-popup-titel').textContent = p.klient_name;

        const warn = p.doppelt
            ? `<div style="color:#dc2626;font-weight:600;margin-bottom:.5rem;">⚠ Doppelbelegung!</div>`
            : '';

        document.getElementById('kl-popup-body').innerHTML = warn + `
            <div class="kl-popup-zeile"><span>Zeit:</span><strong>${p.zeit_von ?? '—'} – ${p.zeit_bis ?? '—'}</strong></div>
            <div class="kl-popup-zeile"><span>Leistung:</span><strong>${p.leistungsart ?? '—'}</strong></div>
            <div class="kl-popup-zeile"><span>Mitarbeiter:</span><strong>${p.benutzer_name}</strong></div>
            ${p.helfer_name ? `<div class="kl-popup-zeile"><span>Helfer:</span><strong>${p.helfer_name}</strong></div>` : ''}
            <div class="kl-popup-zeile"><span>Status:</span><strong>${p.statusLabel}</strong></div>
        `;

        document.getElementById('kl-popup-edit').href   = `/einsaetze/${event.id}/edit`;
        document.getElementById('kl-popup-klient').href = `/klienten/${p.klient_id}`;

        popup.style.display = 'block';
        const x = Math.min(jsEvent.clientX + 10, window.innerWidth  - 300);
        const y = Math.min(jsEvent.clientY + 10, window.innerHeight - 220);
        popup.style.left = x + 'px';
        popup.style.top  = y + 'px';
    }

    window.schliessePopup = function() {
        document.getElementById('kl-popup').style.display = 'none';
    };

    document.addEventListener('click', function(e) {
        const popup = document.getElementById('kl-popup');
        if (popup && !popup.contains(e.target) && !e.target.closest('.fc-event')) {
            popup.style.display = 'none';
        }
    });

    window.addEventListener('pageshow', function(e) {
        if (e.persisted) window.location.reload();
    });
};
