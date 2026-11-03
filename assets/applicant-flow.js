/*
 * Applicant pages: UI behavior only.
 * All data (profile, vacancies, applications) now comes from PHP + MySQL.
 * Replaces assets/ui-model.js and assets/ui-flow.js on:
 *   applicant-verification.php, job-vacancies.php, job-application.php
 */
(() => {
  'use strict';

  const MAX_FILE_BYTES = 5 * 1024 * 1024;
  const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];

  function notify(text, isError) {
    const box = document.getElementById('flow-message');
    if (!box) return;
    box.hidden = false;
    box.classList.toggle('flow-error', !!isError);
    box.setAttribute('role', isError ? 'alert' : 'status');
    box.textContent = text;
    box.focus();
  }

  /* "Not Applicable" disables the PRC number field. */
  function setupLicense(form) {
    const check = form.elements.prcNotApplicable;
    const input = form.elements.prcNumber;
    if (!check || !input) return;
    const update = () => {
      input.disabled = check.checked;
      input.required = !check.checked;
      if (check.checked) input.value = '';
    };
    check.addEventListener('change', update);
    update();
  }

  /* Show chosen file names under each upload control. */
  function setupFileLists(form) {
    form.querySelectorAll('input[type=file]').forEach((input) => {
      input.addEventListener('change', () => {
        const out = form.querySelector('[data-file-list="' + input.name.replace('[]', '') + '"]');
        if (out) out.textContent = [...input.files].map((f) => f.name).join(', ');
      });
    });
  }

  /* Quick client-side file check (the server checks again). */
  function filesProblem(form) {
    for (const input of form.querySelectorAll('input[type=file]')) {
      for (const file of input.files) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED_EXT.includes(ext)) {
          return file.name + ' is not a supported file type (PDF, DOC, DOCX, PNG, JPG).';
        }
        if (file.size > MAX_FILE_BYTES) {
          return file.name + ' is larger than 5 MB.';
        }
      }
    }
    return '';
  }

  document.querySelectorAll('form[data-flow-form]').forEach((form) => {
    setupLicense(form);
    setupFileLists(form);

    form.addEventListener('submit', (event) => {
      if (!form.reportValidity()) {
        event.preventDefault();
        notify('Complete the required fields before continuing.', true);
        return;
      }
      const problem = filesProblem(form);
      if (problem) {
        event.preventDefault();
        notify(problem, true);
        return;
      }
      // Prevent double submission; the form still submits normally.
      const button = form.querySelector('button[type=submit]');
      if (button) setTimeout(() => { button.disabled = true; }, 0);
    });
  });

  /* Server-side error message: bring it into view / focus. */
  const flash = document.getElementById('flow-message');
  if (flash && !flash.hidden) flash.focus();

  /* Log Out button (job-vacancies.php header). */
  const logout = document.getElementById('vacancy-logout');
  if (logout) {
    logout.addEventListener('click', () => {
      location.href = 'authentication/logout.php';
    });
  }

  /* Vacancy cards: expand / collapse details. */
  const grid = document.getElementById('vacancy-grid');
  if (grid) {
    const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

    grid.querySelectorAll('.vacancy-card').forEach((card) => {
      const toggle = card.querySelector('.vacancy-toggle');
      const details = card.querySelector('.vacancy-details');
      const label = card.querySelector('.vacancy-toggle-label');
      const close = card.querySelector('.vacancy-close');
      if (!toggle || !details || !label) return;

      const setOpen = (open) => {
        card.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        details.setAttribute('aria-hidden', String(!open));
        details.inert = !open;
        label.textContent = open ? 'Hide details' : 'View details';
      };

      toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
      });
      if (close) {
        close.addEventListener('click', () => {
          toggle.focus({ preventScroll: true });
          setOpen(false);
          toggle.scrollIntoView({ behavior: reduced() ? 'instant' : 'smooth', block: 'nearest' });
        });
      }
    });

    /* Header search over the cards already on the page. */
    const search = document.getElementById('vacancy-search');
    const searchToggle = document.getElementById('vacancy-search-toggle');
    const searchInput = document.getElementById('vacancy-search-input');
    const searchStatus = document.getElementById('vacancy-search-status');

    if (search && searchToggle && searchInput) {
      let isOpen = false;

      const filter = () => {
        const query = searchInput.value.trim().toLowerCase();
        let count = 0;
        grid.querySelectorAll('.vacancy-card').forEach((card) => {
          const hay = (card.dataset.search || card.textContent).toLowerCase();
          const match = hay.includes(query);
          card.hidden = !match;
          if (match) count++;
        });
        if (searchStatus) {
          searchStatus.hidden = !query;
          searchStatus.textContent = count
            ? count + ' position' + (count === 1 ? '' : 's') + ' found.'
            : 'No positions found. Try a different keyword.';
        }
      };

      const setSearch = (open) => {
        isOpen = open;
        search.classList.toggle('is-open', open);
        searchToggle.setAttribute('aria-expanded', String(open));
        searchToggle.setAttribute('aria-label', open ? 'Close job search' : 'Search job vacancies');
        searchInput.disabled = !open;
        if (open) {
          searchInput.focus();
        } else {
          searchInput.value = '';
          filter();
          searchToggle.focus();
        }
      };

      searchToggle.addEventListener('click', () => setSearch(!isOpen));
      searchInput.addEventListener('input', filter);
      search.addEventListener('submit', (e) => { e.preventDefault(); filter(); });
      search.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { e.preventDefault(); setSearch(false); }
      });
    }
  }
})();
