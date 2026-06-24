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

  // Logout confirmation modal
  const logoutModal = document.getElementById('logoutModal');
  const logoutConfirmBtn = document.getElementById('logoutConfirm');
  const logoutCancelBtn = document.getElementById('logoutCancel');
  let logoutUrl = null;

  // Intercept all logout links
  document.querySelectorAll('a[href="/auth/logout.php"]').forEach((logoutLink) => {
    logoutLink.addEventListener('click', (e) => {
      e.preventDefault();
      logoutUrl = logoutLink.href;
      logoutModal.classList.add('active');
    });
  });

  // Cancel logout
  if (logoutCancelBtn) {
    logoutCancelBtn.addEventListener('click', () => {
      logoutModal.classList.remove('active');
      logoutUrl = null;
    });
  }

  // Confirm logout
  if (logoutConfirmBtn) {
    logoutConfirmBtn.addEventListener('click', () => {
      if (logoutUrl) {
        window.location.href = logoutUrl;
      }
    });
  }

  // Close modal when clicking outside
  if (logoutModal) {
    logoutModal.addEventListener('click', (e) => {
      if (e.target === logoutModal) {
        logoutModal.classList.remove('active');
        logoutUrl = null;
      }
    });
  }
});
