let appCalendar = null;

window.addEventListener('DOMContentLoaded', async () => {
  const calendarEl = document.getElementById('competition-calendar');
  if (!calendarEl || !window.FullCalendar) {
    return;
  }

  // ── Popup element ────────────────────────────────────────────────────────
  const popup = document.createElement('div');
  popup.id = 'cal-popup';
  popup.style.cssText = `
    display:none; position:fixed; z-index:9999;
    background:var(--surface,#1e1e2e); border:1px solid var(--border,#333);
    border-radius:0.75rem; padding:1rem 1.25rem; min-width:220px; max-width:300px;
    box-shadow:0 8px 32px rgba(0,0,0,0.45); font-family:inherit;
  `;
  popup.innerHTML = `
    <button id="cal-popup-close" style="float:right;background:none;border:none;color:var(--text-secondary,#aaa);font-size:1.2rem;cursor:pointer;line-height:1;">&times;</button>
    <p id="cal-popup-cat"  style="margin:0 0 0.3rem;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;"></p>
    <h3 id="cal-popup-title" style="margin:0 0 0.5rem;font-size:1rem;font-family:'Space Grotesk',sans-serif;"></h3>
    <p  id="cal-popup-date"  style="margin:0 0 0.85rem;font-size:0.82rem;color:var(--text-secondary,#aaa);"></p>
    <a  id="cal-popup-link" href="#" style="display:inline-block;padding:0.45rem 1rem;border-radius:0.5rem;background:var(--accent-primary,#6c63ff);color:#fff;font-size:0.85rem;font-weight:600;text-decoration:none;">View details →</a>
  `;
  document.body.appendChild(popup);

  document.getElementById('cal-popup-close').addEventListener('click', () => { popup.style.display = 'none'; });
  document.addEventListener('click', (e) => {
    if (!popup.contains(e.target) && !e.target.closest('.fc-event')) {
      popup.style.display = 'none';
    }
  });

  // ── Fetch events ─────────────────────────────────────────────────────────
  async function fetchCalendarEvents() {
    try {
      const res = await fetch(`/api/competitions.php?format=calendar&_t=${Date.now()}`, {
        headers: { 'Cache-Control': 'no-cache' }
      });
      if (!res.ok) return [];
      const data = await res.json();
      return Array.isArray(data) ? data : [];
    } catch {
      return [];
    }
  }

  // ── Format date range label ───────────────────────────────────────────────
  function formatDateRange(start, end, allDay) {
    const opts = { month: 'short', day: 'numeric', year: 'numeric' };
    const s = new Date(start);
    if (!end || start === end) {
      if (allDay) return s.toLocaleDateString(undefined, opts);
      return s.toLocaleString(undefined, { ...opts, hour: '2-digit', minute: '2-digit' });
    }
    const e = new Date(end);
    // For all-day events FullCalendar end is exclusive, show day before
    const displayEnd = allDay ? new Date(e.getTime() - 86400000) : e;
    const startStr = s.toLocaleDateString(undefined, opts);
    const endStr   = displayEnd.toLocaleDateString(undefined, opts);
    return startStr === endStr ? startStr : `${startStr} – ${endStr}`;
  }

  // ── Build calendar ────────────────────────────────────────────────────────
  const initialEvents = await fetchCalendarEvents();

  appCalendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'dayGridMonth,timeGridWeek,refreshCalendar',
    },
    customButtons: {
      refreshCalendar: {
        text: 'Refresh Calendar',
        click: async () => {
          const updated = await fetchCalendarEvents();
          appCalendar.removeAllEvents();
          appCalendar.addEventSource(updated);
        },
      },
    },
    events: initialEvents,

    // Show blocks on month view, timed bars on week view
    eventDisplay: 'block',

    eventClick(info) {
      info.jsEvent.preventDefault();
      info.jsEvent.stopPropagation();

      const ev    = info.event;
      const props = ev.extendedProps;
      const colors = {
        CTF:'#ff4757', Hackathon:'#6c63ff', Robotics:'#54e98a',
        Gaming:'#ff9a4a', Coding:'#61de8a', 'AI/ML':'#ff6b81', Other:'#869486'
      };
      const color = colors[props.category] || colors.Other;

      document.getElementById('cal-popup-cat').textContent   = props.category || '';
      document.getElementById('cal-popup-cat').style.color   = color;
      document.getElementById('cal-popup-title').textContent = ev.title;
      document.getElementById('cal-popup-date').textContent  =
        formatDateRange(ev.startStr, ev.endStr, ev.allDay);

      const link = document.getElementById('cal-popup-link');
      link.href              = ev.url || '#';
      link.style.background  = color;

      // Position popup near the click
      const x = Math.min(info.jsEvent.clientX + 12, window.innerWidth  - 320);
      const y = Math.min(info.jsEvent.clientY + 12, window.innerHeight - 220);
      popup.style.left    = x + 'px';
      popup.style.top     = y + 'px';
      popup.style.display = 'block';
    },
  });

  window.appCalendar = appCalendar;

  // ── Render when tab becomes visible ──────────────────────────────────────
  const calendarPanel  = document.querySelector('[data-view-panel="calendar"]');
  const toggleButtons  = document.querySelectorAll('[data-view-toggle]');
  let   calendarRendered = false;

  function renderCalendarIfNeeded() {
    if (!calendarRendered && calendarPanel && !calendarPanel.classList.contains('hidden')) {
      appCalendar.render();
      calendarRendered = true;
    }
  }

  toggleButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      if (btn.getAttribute('data-view-toggle') === 'calendar') {
        setTimeout(renderCalendarIfNeeded, 100);
      }
    });
  });

  // ResizeObserver fallback
  if (window.ResizeObserver && calendarPanel) {
    new ResizeObserver(renderCalendarIfNeeded).observe(calendarPanel);
  }
});
