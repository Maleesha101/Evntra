let appCalendar = null;
let calendarEvents = [];

window.addEventListener('DOMContentLoaded', async () => {
  const calendarEl = document.getElementById('competition-calendar');
  if (!calendarEl || !window.FullCalendar) {
    console.log('Calendar element or FullCalendar not found');
    return;
  }

  console.log('Calendar DOM element found, starting initialization...');

  // Function to fetch calendar events with proper cache-busting
  async function fetchCalendarEvents() {
    try {
      const timestamp = new Date().getTime();
      const url = `/api/competitions.php?format=calendar&_nocache=${timestamp}`;
      
      console.log('Fetching calendar events from:', url);
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0'
        }
      });
      
      if (!response.ok) {
        console.error('Failed to fetch calendar events. Status:', response.status);
        return [];
      }
      
      const events = await response.json();
      console.log('✓ Fetched calendar events:', events.length, 'events', events);
      return Array.isArray(events) ? events : [];
    } catch (error) {
      console.error('Error fetching calendar events:', error);
      return [];
    }
  }

  // Fetch events immediately
  console.log('Fetching initial calendar events...');
  calendarEvents = await fetchCalendarEvents();

  // Create the calendar
  console.log('Creating FullCalendar instance...');
  appCalendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,refreshCalendar',
    },
    customButtons: {
      refreshCalendar: {
        text: 'Refresh Calendar',
        click: async function() {
          console.log('Manual refresh triggered...');
          const updatedEvents = await fetchCalendarEvents();
          appCalendar.removeAllEvents();
          appCalendar.addEventSource(updatedEvents);
          console.log('Calendar manually refreshed');
        }
      }
    },
    events: calendarEvents,
    eventClick(info) {
      info.jsEvent.preventDefault();
      if (info.event.url) {
        window.location.href = info.event.url;
      }
    },
  });

  window.appCalendar = appCalendar;

  // Detect when calendar view becomes visible and render it
  const calendarPanel = document.querySelector('[data-view-panel="calendar"]');
  const toggleButtons = document.querySelectorAll('[data-view-toggle]');
  
  let calendarRendered = false;

  function renderCalendarIfNeeded() {
    if (!calendarRendered && calendarPanel && !calendarPanel.classList.contains('hidden')) {
      console.log('Calendar panel is now visible, rendering calendar...');
      try {
        appCalendar.render();
        calendarRendered = true;
        console.log('✓ Calendar rendered successfully');
        startAutoRefresh();
      } catch (e) {
        console.error('Error rendering calendar:', e);
      }
    }
  }

  // Handle tab clicks
  toggleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const view = button.getAttribute('data-view-toggle');
      console.log('Tab clicked:', view);
      
      if (view === 'calendar') {
        setTimeout(() => {
          renderCalendarIfNeeded();
        }, 100);
      }
    });
  });

  // Auto-refresh functionality
  let autoRefreshInterval = null;

  function startAutoRefresh() {
    if (autoRefreshInterval) {
      console.log('Auto-refresh already running');
      return;
    }
    
    console.log('Starting auto-refresh (every 10 seconds)...');
    autoRefreshInterval = setInterval(async () => {
      const updatedEvents = await fetchCalendarEvents();
      const currentEventCount = appCalendar.getEvents().length;
      const newEventCount = updatedEvents.length;
      
      if (newEventCount !== currentEventCount) {
        console.log(`📊 Event count changed: ${currentEventCount} → ${newEventCount}`);
        appCalendar.removeAllEvents();
        appCalendar.addEventSource(updatedEvents);
        console.log('✓ Calendar updated with new events');
      }
    }, 10000);
  }

  function stopAutoRefresh() {
    if (autoRefreshInterval) {
      console.log('Stopping auto-refresh');
      clearInterval(autoRefreshInterval);
      autoRefreshInterval = null;
    }
  }

  // Handle page visibility
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      console.log('Page hidden, stopping auto-refresh');
      stopAutoRefresh();
    } else {
      console.log('Page visible, starting auto-refresh');
      if (calendarPanel && !calendarPanel.classList.contains('hidden')) {
        startAutoRefresh();
      }
    }
  });

  // Use ResizeObserver to detect visibility changes
  if (window.ResizeObserver && calendarPanel) {
    const resizeObserver = new ResizeObserver(() => {
      renderCalendarIfNeeded();
      if (calendarRendered && !document.hidden && !calendarPanel.classList.contains('hidden')) {
        if (!autoRefreshInterval) {
          startAutoRefresh();
        }
      }
    });
    resizeObserver.observe(calendarPanel);
  }

  console.log('✓ Calendar initialization complete');
});


