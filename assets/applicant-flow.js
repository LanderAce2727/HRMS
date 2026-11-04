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

  /* ---------- per-field errors: red border + message under the field ---------- */

  const RULES = {
    contactNumber: {
      test: (v) => /^[+\d ()-]{7,20}$/.test(v),
      message: 'Enter a valid contact number (digits, +, spaces or dashes).',
    },
    prcNumber: {
      test: (v) => /^[A-Za-z0-9][A-Za-z0-9 \-\/]{0,39}$/.test(v),
      message: 'Enter a valid PRC license number, or tick Not Applicable.',
    },
    experience: {
      test: (v) => /^\d{1,2}$/.test(v) && Number(v) <= 60,
      message: 'Enter whole years of experience, from 0 to 60.',
    },
  };

  function fieldKey(control) {
    return control.name.replace('[]', '');
  }

  function messageBox(control, create) {
    const label = control.closest('label');
    if (!label) return null;
    let box = label.querySelector('.field-error');
    if (!box && create) {
      box = document.createElement('small');
      box.className = 'field-error';
      box.id = 'err-' + fieldKey(control);
      box.setAttribute('role', 'alert');
      label.appendChild(box);
    }
    return box;
  }

  function showError(control, message) {
    const label = control.closest('label');
    const box = messageBox(control, true);
    if (!label || !box) return;
    label.classList.add('has-error');
    control.setAttribute('aria-invalid', 'true');
    control.setAttribute('aria-describedby', box.id);
    box.textContent = message;
    box.hidden = false;
  }

  function clearError(control) {
    const label = control.closest('label');
    const box = messageBox(control, false);
    if (label) label.classList.remove('has-error');
    control.removeAttribute('aria-invalid');
    if (box) {
      box.textContent = '';
      box.hidden = true;
    }
  }

  /* Returns an error message for the control, or '' when it is fine. */
  function problemFor(control) {
    if (control.disabled || control.readOnly || control.type === 'hidden' || control.type === 'checkbox') {
      return '';
    }

    if (control.type === 'file') {
      if (control.required && control.files.length === 0) return 'Please attach this file.';
      for (const file of control.files) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED_EXT.includes(ext)) {
          return file.name + ' is not supported. Use PDF, DOC, DOCX, PNG or JPG.';
        }
        if (file.size > MAX_FILE_BYTES) return file.name + ' is larger than 5 MB.';
      }
      return '';
    }

    const value = control.value.trim();
    if (value === '') {
      if (!control.required) return '';
      if (control.name === 'prcNumber') return 'Enter your PRC license number, or tick Not Applicable.';
      return control.tagName === 'SELECT' ? 'Please choose an option.' : 'This field is required.';
    }
    const rule = RULES[control.name];
    if (rule && !rule.test(value)) return rule.message;
    return '';
  }

  /* "Not Applicable" disables the PRC number field. */
  function setupLicense(form) {
    const check = form.elements.prcNotApplicable;
    const input = form.elements.prcNumber;
    if (!check || !input) return;
    const update = () => {
      input.disabled = check.checked;
      input.required = !check.checked;
      if (check.checked) {
        input.value = '';
        clearError(input);
      }
    };
    check.addEventListener('change', update);
    update();
  }

  /* Show chosen file names under each upload control. */
  function setupFileLists(form) {
    form.querySelectorAll('input[type=file]').forEach((input) => {
      input.addEventListener('change', () => {
        const out = form.querySelector('[data-file-list="' + fieldKey(input) + '"]');
        if (out) out.textContent = [...input.files].map((f) => f.name).join(', ');
      });
    });
  }

  document.querySelectorAll('form[data-flow-form]').forEach((form) => {
    setupLicense(form);
    setupFileLists(form);

    // Typing / choosing clears that field's error straight away.
    const fields = 'input, select, textarea';
    form.addEventListener('input', (e) => { if (e.target.matches(fields)) clearError(e.target); });
    form.addEventListener('change', (e) => { if (e.target.matches(fields)) clearError(e.target); });

    form.addEventListener('submit', (event) => {
      let first = null;
      form.querySelectorAll(fields).forEach((control) => {
        const message = problemFor(control);
        if (message) {
          showError(control, message);
          if (!first) first = control;
        } else {
          clearError(control);
        }
      });

      if (first) {
        event.preventDefault();
        first.focus();
        return;
      }

      // Prevent double submission; the form still submits normally.
      const button = form.querySelector('button[type=submit]');
      if (button) setTimeout(() => { button.disabled = true; }, 0);
    });
  });

  /* After a server-side validation failure: jump to the first bad field. */
  const firstBad = document.querySelector(
    '.has-error input:not([type=checkbox]), .has-error select, .has-error textarea'
  );
  const flash = document.getElementById('flow-message');
  if (firstBad) {
    firstBad.focus();
  } else if (flash && !flash.hidden) {
    flash.focus();
  }

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
