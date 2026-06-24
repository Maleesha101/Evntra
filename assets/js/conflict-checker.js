window.addEventListener('DOMContentLoaded', () => {
  const startInput = document.querySelector('[data-event-start]');
  const endInput = document.querySelector('[data-event-end]');
  const categoryInput = document.querySelector('[data-category-input]');
  const warningBox = document.querySelector('[data-conflict-warning]');

  if (!startInput || !endInput || !categoryInput || !warningBox) {
    return;
  }

  let timer;
  const checkConflict = async () => {
    const params = new URLSearchParams({
      event_start: startInput.value,
      event_end: endInput.value,
      category: categoryInput.value,
    });
    const response = await fetch(`/api/check-conflict.php?${params.toString()}`);
    const payload = await response.json();
    if (payload.conflicts?.length) {
      warningBox.innerHTML = `
        <div class="conflict-card-inner">
          <div class="conflict-icon-wrap">
            <span class="material-symbols-outlined">warning</span>
          </div>
          <div class="conflict-content">
            <h3 class="conflict-title">Live Conflict Alert</h3>
            <p class="conflict-text">${payload.message}</p>
          </div>
        </div>
      `;
      warningBox.className = 'conflict-warning-card active';
    } else {
      warningBox.innerHTML = '';
      warningBox.className = 'form-help';
    }
  };

  [startInput, endInput, categoryInput].forEach((field) => {
    field.addEventListener('change', () => {
      clearTimeout(timer);
      timer = setTimeout(checkConflict, 250);
    });
  });
});
