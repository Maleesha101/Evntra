const searchState = {
  page: 1,
  sort: 'newest',
};

function competitionCardTemplate(item) {
  const status = item.max_participants && item.registered_count >= item.max_participants ? 'Full' : item.registration_status;
  
  const categoryImages = {
    'CTF': 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=600',
    'Hackathon': 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&q=80&w=600',
    'Robotics': 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&q=80&w=600',
    'Gaming': 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&q=80&w=600',
    'Coding': 'https://images.unsplash.com/photo-1605379399642-870262d3d051?auto=format&fit=crop&q=80&w=600',
    'AI/ML': 'https://images.unsplash.com/photo-1677442136019-21780efad99a?auto=format&fit=crop&q=80&w=600',
    'Other': 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&q=80&w=600'
  };
  
  const image = item.banner_image || categoryImages[item.category] || categoryImages['Other'];
  
  const categoryIcons = {
    'CTF': 'vpn_key',
    'Hackathon': 'terminal',
    'Robotics': 'precision_manufacturing',
    'Gaming': 'sports_esports',
    'Coding': 'code',
    'AI/ML': 'smart_toy',
    'Other': 'category'
  };
  const icon = categoryIcons[item.category] || 'category';
  
  const isBookmarked = item.is_bookmarked;
  const bookmarkClass = isBookmarked ? 'bookmark-badge-btn bookmarked' : 'bookmark-badge-btn';
  const bookmarkIcon = isBookmarked ? 'bookmark' : 'bookmark_border';
  
  const venueType = item.venue.toLowerCase() === 'online' ? 'Online' : 'On-Campus';
  const statusClass = status.toLowerCase() === 'open' 
    ? 'bg-primary/20 backdrop-blur-md border border-primary/30 text-primary' 
    : (status.toLowerCase() === 'full' 
       ? 'bg-amber-500/20 backdrop-blur-md border border-amber-500/30 text-amber-500' 
       : 'bg-white/10 backdrop-blur-md border border-white/20 text-on-surface');
       
  const capText = item.max_participants 
    ? `${item.registered_count}/${item.max_participants} Spots` 
    : `${item.registered_count} Registered`;

  return `
    <article class="browse-card ${item.category.toLowerCase()}">
      <div class="browse-card-image-wrap">
        <img class="browse-card-image" src="${image}" alt="${item.title}">
        <div class="browse-card-badges-left">
          <span class="badge ${statusClass}" style="font-size: 10px; padding: 0.25rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.25rem;">${status}</span>
          <span class="badge" style="background: rgba(17, 19, 23, 0.6); backdrop-filter: blur(8px); border: 1px solid var(--border); font-size: 10px; padding: 0.25rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; color: var(--text-primary); display: inline-flex; align-items: center; gap: 0.25rem;">${venueType}</span>
        </div>
        <button class="${bookmarkClass}" data-bookmark-toggle data-competition-id="${item.id}" aria-label="Bookmark">
          <span class="material-symbols-outlined" style="font-size: 18px;">${bookmarkIcon}</span>
        </button>
      </div>
      <div class="browse-card-body">
        <div class="browse-card-cat">
          <span class="material-symbols-outlined" style="font-size: 14px;">${icon}</span>
          <span>${item.category}</span>
        </div>
        <h3 class="browse-card-title"><a href="${item.url}">${item.title}</a></h3>
        <p class="browse-card-desc">${item.description}</p>
        <div class="browse-card-footer">
          <div style="display: flex; align-items: center; gap: 0.35rem; color: var(--text-secondary);">
            <span class="material-symbols-outlined" style="font-size: 16px;">group</span>
            <span>${capText}</span>
          </div>
          <a class="browse-card-details-link" href="${item.url}">
            <span>Details</span>
            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
          </a>
        </div>
      </div>
    </article>
  `;
}

function renderCompetitionResults(payload) {
  const grid = document.querySelector('[data-competition-grid]');
  const pagination = document.querySelector('[data-pagination]');
  if (!grid) return;

  grid.innerHTML = payload.items.map(competitionCardTemplate).join('') || '<div class="card"><div class="card-body"><p>No competitions match your filters.</p></div></div>';

  if (pagination) {
    const buttons = [];
    for (let i = 1; i <= payload.pages; i += 1) {
      buttons.push(`<button class="tab-button ${i === payload.page ? 'active' : ''}" data-page="${i}">${i}</button>`);
    }
    pagination.innerHTML = buttons.join('');
    pagination.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => {
        searchState.page = Number(button.getAttribute('data-page'));
        fetchCompetitions();
      });
    });
  }

  grid.querySelectorAll('[data-bookmark-toggle]').forEach((button) => {
    button.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      const competitionId = button.getAttribute('data-competition-id');
      if (!competitionId) return;
      const response = await fetch('/api/bookmark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ competition_id: competitionId })
      });
      const data = await response.json();
      const icon = button.querySelector('.material-symbols-outlined');
      if (data.bookmarked) {
        button.classList.add('bookmarked');
        if (icon) icon.textContent = 'bookmark';
      } else {
        button.classList.remove('bookmarked');
        if (icon) icon.textContent = 'bookmark_border';
      }
    });
  });
}

function renderActiveChips() {
  const container = document.querySelector('[data-active-chips]');
  if (!container) return;

  const chips = [];

  // Category chips
  document.querySelectorAll('[data-category-filter]:checked').forEach((el) => {
    chips.push({
      label: el.value,
      type: 'category',
      element: el
    });
  });

  // Venue chip
  const venue = document.querySelector('[data-venue-filter]')?.value;
  if (venue && venue !== 'all') {
    chips.push({
      label: `Venue: ${venue}`,
      type: 'venue',
      element: document.querySelector('[data-venue-filter]')
    });
  }

  // Status chip
  const status = document.querySelector('[data-status-filter]')?.value;
  if (status && status !== 'all') {
    chips.push({
      label: `Status: ${status}`,
      type: 'status',
      element: document.querySelector('[data-status-filter]')
    });
  }

  // Search chip
  const searchInput = document.querySelector('[data-search-input]');
  if (searchInput && searchInput.value.trim() !== '') {
    chips.push({
      label: `Search: "${searchInput.value}"`,
      type: 'search',
      element: searchInput
    });
  }

  if (chips.length === 0) {
    container.innerHTML = '';
    container.style.display = 'none';
    return;
  }

  container.style.display = 'flex';
  container.innerHTML = chips.map((chip, index) => `
    <div class="active-chip" style="display:inline-flex; align-items:center; gap:0.35rem; background:rgba(84,233,138,0.1); border:1px solid rgba(84,233,138,0.3); color:var(--accent-primary); border-radius:9999px; padding:0.25rem 0.75rem; font-size:0.8rem; font-weight:500;" data-chip-index="${index}">
      <span>${chip.label}</span>
      <button type="button" style="background:transparent; border:none; color:inherit; display:flex; align-items:center; cursor:pointer; padding:0;" class="chip-remove-btn"><span class="material-symbols-outlined" style="font-size:14px;">close</span></button>
    </div>
  `).join('');

  container.querySelectorAll('.chip-remove-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      const idx = btn.closest('.active-chip').getAttribute('data-chip-index');
      const chip = chips[idx];
      if (chip.type === 'category') {
        chip.element.checked = false;
      } else if (chip.type === 'search') {
        chip.element.value = '';
      } else {
        chip.element.value = 'all';
      }
      searchState.page = 1;
      fetchCompetitions();
    });
  });
}

async function fetchCompetitions() {
  const searchInput = document.querySelector('[data-search-input]');
  const categoryValues = [...document.querySelectorAll('[data-category-filter]:checked')].map((el) => el.value).join(',');
  const sort = document.querySelector('[data-sort-select]')?.value || searchState.sort;
  const status = document.querySelector('[data-status-filter]')?.value || 'all';
  const venue = document.querySelector('[data-venue-filter]')?.value || 'all';
  const dateFrom = document.querySelector('[data-date-from]')?.value || '';
  const dateTo = document.querySelector('[data-date-to]')?.value || '';
  const search = searchInput?.value || '';

  const params = new URLSearchParams({
    search,
    category: categoryValues,
    sort,
    status,
    venue,
    date_from: dateFrom,
    date_to: dateTo,
    page: String(searchState.page),
    per_page: '10',
  });

  const response = await fetch(`/api/competitions.php?${params.toString()}`);
  const payload = await response.json();
  renderCompetitionResults(payload);
  renderActiveChips();
}

window.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('[data-search-input]');
  if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        searchState.page = 1;
        fetchCompetitions();
      }, 250);
    });
  }

  document.querySelectorAll('[data-category-filter], [data-sort-select], [data-status-filter], [data-venue-filter], [data-date-from], [data-date-to]').forEach((field) => {
    field.addEventListener('change', () => {
      searchState.page = 1;
      searchState.sort = document.querySelector('[data-sort-select]')?.value || 'newest';
      fetchCompetitions();
    });
  });

  const toggleButtons = document.querySelectorAll('[data-view-toggle]');
  toggleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const view = button.getAttribute('data-view-toggle');
      document.querySelectorAll('[data-view-panel]').forEach((panel) => panel.classList.add('hidden'));
      document.querySelectorAll('[data-view-toggle]').forEach((btn) => btn.classList.remove('active'));
      document.querySelector(`[data-view-panel="${view}"]`)?.classList.remove('hidden');
      button.classList.add('active');

      if (view === 'calendar' && window.appCalendar) {
        setTimeout(() => {
          window.appCalendar.updateSize();
        }, 50);
      }
    });
  });

  const resetButton = document.querySelector('[data-reset-filters]');
  if (resetButton) {
    resetButton.addEventListener('click', () => {
      const searchInput = document.querySelector('[data-search-input]');
      if (searchInput) searchInput.value = '';
      document.querySelectorAll('[data-category-filter]:checked').forEach((el) => el.checked = false);
      const sortSelect = document.querySelector('[data-sort-select]');
      if (sortSelect) sortSelect.selectedIndex = 0;
      const statusFilter = document.querySelector('[data-status-filter]');
      if (statusFilter) statusFilter.selectedIndex = 0;
      const venueFilter = document.querySelector('[data-venue-filter]');
      if (venueFilter) venueFilter.selectedIndex = 0;
      const dateFrom = document.querySelector('[data-date-from]');
      if (dateFrom) dateFrom.value = '';
      const dateTo = document.querySelector('[data-date-to]');
      if (dateTo) dateTo.value = '';
      
      searchState.page = 1;
      searchState.sort = 'newest';
      fetchCompetitions();
    });
  }

  if (document.querySelector('[data-competition-grid]')) {
    fetchCompetitions();
  }
});
