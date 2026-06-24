document.addEventListener('DOMContentLoaded', () => {
  // Password toggle functionality
  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      const input = button.closest('.password-input-wrapper').querySelector('input[type="password"], input[type="text"]');
      const eyeIcon = button.querySelector('.eye-icon');
      const eyeOffIcon = button.querySelector('.eye-off-icon');

      if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
      } else {
        input.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
      }
    });
  });

  const toggle = document.querySelector('[data-notification-toggle]');
  const panel = document.querySelector('[data-notification-panel]');
  const markAllRead = document.querySelector('[data-mark-all-read]');

  if (toggle && panel) {
    toggle.addEventListener('click', () => {
      panel.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
      if (!panel.contains(event.target) && !toggle.contains(event.target)) {
        panel.classList.remove('open');
      }
    });
  }

  if (markAllRead) {
    markAllRead.addEventListener('click', async () => {
      const items = [...document.querySelectorAll('[data-notification-id]')];
      try {
        await fetch('/api/notifications.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'read-all' })
        });
      } catch (err) {
        console.error('Error marking all notifications as read:', err);
      }
      const badge = document.querySelector('[data-notification-count]');
      if (badge) {
        badge.style.display = 'none';
      }
      items.forEach((item) => item.classList.remove('unread'));
    });
  }

  document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
      const url = button.getAttribute('data-copy-link');
      if (!url) return;
      await navigator.clipboard.writeText(url);
      const previous = button.textContent;
      button.textContent = 'Copied';
      setTimeout(() => {
        button.textContent = previous;
      }, 1500);
    });
  });

  document.querySelectorAll('[data-open-modal]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.querySelector(button.getAttribute('data-open-modal'));
      if (target) target.classList.add('open');
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', () => {
      const modal = button.closest('.modal');
      if (modal) modal.classList.remove('open');
    });
  });

  document.querySelectorAll('[data-auto-submit]').forEach((field) => {
    field.addEventListener('change', () => field.form?.requestSubmit());
  });

  // Handle header search bar
  const headerSearchInput = document.querySelector('.search-input');
  const headerSearchDropdown = document.querySelector('[data-search-dropdown]');

  if (headerSearchInput) {
    let searchTimer;

    const closeHeaderDropdown = () => {
      if (!headerSearchDropdown) return;
      headerSearchDropdown.hidden = true;
    };

    const renderHeaderSearchResults = (results) => {
      if (!headerSearchDropdown) return;
      if (!results || results.length === 0) {
        headerSearchDropdown.innerHTML = '<div class="search-dropdown-empty">No matches found.</div>';
        headerSearchDropdown.hidden = false;
        return;
      }

      headerSearchDropdown.innerHTML = results.map((item) => `
        <a href="${item.url}" class="search-dropdown-item">
          <div>
            <div class="search-dropdown-item-title">${item.title}</div>
            <div class="search-dropdown-item-meta"><span class="material-symbols-outlined" style="font-size:16px;">${item.category === 'Online' ? 'public' : 'location_on'}</span>${item.category}</div>
          </div>
          <span class="material-symbols-outlined" style="font-size:20px; color: var(--text-secondary);">arrow_forward</span>
        </a>
      `).join('');
      headerSearchDropdown.hidden = false;
    };

    const fetchHeaderSearch = async (query) => {
      if (!query || query.length < 1) {
        closeHeaderDropdown();
        return;
      }

      try {
        const params = new URLSearchParams({ search: query, page: '1', per_page: '3' });
        const response = await fetch(`/api/competitions.php?${params.toString()}`);
        const payload = await response.json();
        renderHeaderSearchResults(payload.items || []);
      } catch (err) {
        headerSearchDropdown.innerHTML = '<div class="search-dropdown-empty">Search failed. Please try again.</div>';
        headerSearchDropdown.hidden = false;
      }
    };

    headerSearchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = window.setTimeout(() => {
        fetchHeaderSearch(headerSearchInput.value.trim());
      }, 250);
    });

    headerSearchInput.addEventListener('focus', () => {
      if (headerSearchInput.value.trim()) {
        fetchHeaderSearch(headerSearchInput.value.trim());
      }
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.search-container')) {
        closeHeaderDropdown();
      }
    });

    headerSearchInput.addEventListener('keypress', (e) => {
      if (e.key !== 'Enter') return;

      const query = headerSearchInput.value.trim();
      if (!query) return;

      const sidebarSearch = document.querySelector('[data-search-input]');
      
      // If on browse page, update sidebar search and trigger search
      if (sidebarSearch) {
        sidebarSearch.value = query;
        if (window.searchState) {
          window.searchState.page = 1;
        }
        if (window.fetchCompetitions) {
          window.fetchCompetitions();
        }
        headerSearchInput.value = '';
        closeHeaderDropdown();
      } else {
        // Otherwise, redirect to browse page with search parameter
        window.location.href = `/student/browse.php?search=${encodeURIComponent(query)}`;
      }
    });
  }
});
