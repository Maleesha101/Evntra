window.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-competition-form]');
  if (!form) return;

  const panels = [...form.querySelectorAll('[data-step-panel]')];
  const pills = [...form.querySelectorAll('[data-step-pill]')];
  let currentStep = 0;

  // Auto-populate date fields with sensible defaults
  const registrationStartInput = form.querySelector('[name="registration_start"]');
  const registrationEndInput = form.querySelector('[name="registration_end"]');
  const eventStartInput = form.querySelector('[name="event_start"]');
  const eventEndInput = form.querySelector('[name="event_end"]');

  if (registrationStartInput && !registrationStartInput.value) {
    // Set registration start to now
    const now = new Date();
    registrationStartInput.value = now.toISOString().slice(0, 16);
  }

  if (registrationEndInput && !registrationEndInput.value) {
    // Set registration end to 7 days from now
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 7);
    registrationEndInput.value = endDate.toISOString().slice(0, 16);
  }

  if (eventStartInput && !eventStartInput.value) {
    // Set event start to 8 days from now
    const eventDate = new Date();
    eventDate.setDate(eventDate.getDate() + 8);
    eventStartInput.value = eventDate.toISOString().slice(0, 16);
  }

  if (eventEndInput && !eventEndInput.value) {
    // Set event end to 8 days from now + 2 hours
    const eventDate = new Date();
    eventDate.setDate(eventDate.getDate() + 8);
    eventDate.setHours(eventDate.getHours() + 2);
    eventEndInput.value = eventDate.toISOString().slice(0, 16);
  }

  const render = () => {
    panels.forEach((panel, index) => panel.classList.toggle('active', index === currentStep));
    pills.forEach((pill, index) => pill.classList.toggle('active', index === currentStep));
  };

  form.addEventListener('click', (event) => {
    const nextButton = event.target.closest('[data-next-step]');
    const backButton = event.target.closest('[data-back-step]');
    if (nextButton) {
      currentStep = Math.min(panels.length - 1, currentStep + 1);
      render();
    }
    if (backButton) {
      currentStep = Math.max(0, currentStep - 1);
      render();
    }
  });

  render();
});
