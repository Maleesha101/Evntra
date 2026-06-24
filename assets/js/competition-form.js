window.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-competition-form]');
  if (!form) return;

  const panels = [...form.querySelectorAll('[data-step-panel]')];
  const pills = [...form.querySelectorAll('[data-step-pill]')];
  let currentStep = 0;

  // Add CSS for validation error styles
  const style = document.createElement('style');
  style.textContent = `
    input.field-error,
    textarea.field-error,
    select.field-error {
      border-color: #ff4757 !important;
      background-color: rgba(255, 71, 87, 0.05) !important;
    }
    .field-error-message {
      color: #ff4757;
      font-size: 0.85rem;
      margin-top: 0.25rem;
      font-weight: 500;
    }
    .form-group.has-error input,
    .form-group.has-error textarea,
    .form-group.has-error select {
      border-color: #ff4757 !important;
      background-color: rgba(255, 71, 87, 0.05) !important;
    }
  `;
  document.head.appendChild(style);

  // Auto-populate date fields with sensible defaults
  const registrationStartInput = form.querySelector('[name="registration_start"]');
  const registrationEndInput = form.querySelector('[name="registration_end"]');
  const eventStartInput = form.querySelector('[name="event_start"]');
  const eventEndInput = form.querySelector('[name="event_end"]');

  if (registrationStartInput && !registrationStartInput.value) {
    const now = new Date();
    registrationStartInput.value = now.toISOString().slice(0, 16);
  }

  if (registrationEndInput && !registrationEndInput.value) {
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 7);
    registrationEndInput.value = endDate.toISOString().slice(0, 16);
  }

  if (eventStartInput && !eventStartInput.value) {
    const eventDate = new Date();
    eventDate.setDate(eventDate.getDate() + 8);
    eventStartInput.value = eventDate.toISOString().slice(0, 16);
  }

  if (eventEndInput && !eventEndInput.value) {
    const eventDate = new Date();
    eventDate.setDate(eventDate.getDate() + 8);
    eventDate.setHours(eventDate.getHours() + 2);
    eventEndInput.value = eventDate.toISOString().slice(0, 16);
  }

  // Validation rules for each step
  const stepValidations = {
    0: [
      { field: 'title', message: 'Competition title is required' },
      { field: 'category', message: 'Category is required' },
      { field: 'description', message: 'Description is required' },
    ],
    1: [
      { field: 'registration_start', message: 'Registration start date is required' },
      { field: 'registration_end', message: 'Registration end date is required' },
      { field: 'event_start', message: 'Event start date is required' },
      { field: 'event_end', message: 'Event end date is required' },
      { field: 'venue', message: 'Venue is required' },
    ],
    2: [], // Last step, no validation needed before submit
  };

  // Validate a specific field
  const validateField = (fieldName) => {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return true;

    const formGroup = field.closest('.form-group');
    const isValid = field.value.trim() !== '';

    if (isValid) {
      field.classList.remove('field-error');
      if (formGroup) formGroup.classList.remove('has-error');
      const errorMsg = formGroup?.querySelector('.field-error-message');
      if (errorMsg) errorMsg.remove();
    } else {
      field.classList.add('field-error');
      if (formGroup) formGroup.classList.add('has-error');
      let errorMsg = formGroup?.querySelector('.field-error-message');
      if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'field-error-message';
        const validation = stepValidations[currentStep]?.find(v => v.field === fieldName);
        errorMsg.textContent = validation?.message || 'This field is required';
        formGroup?.appendChild(errorMsg);
      }
    }

    return isValid;
  };

  // Validate current step
  const validateCurrentStep = () => {
    const validations = stepValidations[currentStep] || [];
    let isValid = true;

    validations.forEach(({ field }) => {
      if (!validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  };

  // Add live validation on field blur
  form.addEventListener('blur', (event) => {
    const field = event.target;
    if (field.matches('input, textarea, select')) {
      const fieldName = field.name;
      const currentStepValidations = stepValidations[currentStep] || [];
      if (currentStepValidations.some(v => v.field === fieldName)) {
        validateField(fieldName);
      }
    }
  }, true);

  const render = () => {
    panels.forEach((panel, index) => panel.classList.toggle('active', index === currentStep));
    pills.forEach((pill, index) => pill.classList.toggle('active', index === currentStep));
    
    // Clear error messages when switching steps
    form.querySelectorAll('.field-error-message').forEach(el => el.remove());
    form.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
    form.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));
  };

  form.addEventListener('click', (event) => {
    const nextButton = event.target.closest('[data-next-step]');
    const backButton = event.target.closest('[data-back-step]');
    
    if (nextButton) {
      // Validate current step before moving to next
      if (!validateCurrentStep()) {
        event.preventDefault();
        return;
      }
      currentStep = Math.min(panels.length - 1, currentStep + 1);
      render();
    }
    
    if (backButton) {
      currentStep = Math.max(0, currentStep - 1);
      render();
    }
  });

  // Validate on form submit
  form.addEventListener('submit', (event) => {
    if (!validateCurrentStep()) {
      event.preventDefault();
      alert('Please fill in all required fields before submitting.');
    }
  });

  render();
});
