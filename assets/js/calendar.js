window.addEventListener('DOMContentLoaded', async () => {
  const calendarEl = document.getElementById('competition-calendar');
  if (!calendarEl || !window.FullCalendar) return;

  const response = await fetch('/api/competitions.php?format=calendar');
  const events = await response.json();

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    allDaySlot: false,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek',
    },
    events,
    eventClick(info) {
      info.jsEvent.preventDefault();
      if (info.event.url) {
        window.location.href = info.event.url;
      }
    },
  });

  calendar.render();
  window.appCalendar = calendar;

  if (window.ResizeObserver) {
    const resizeObserver = new ResizeObserver(() => {
      calendar.updateSize();
    });
    resizeObserver.observe(calendarEl);
  }
});
