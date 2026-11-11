(() => {
  'use strict';

  const dialog = document.createElement('dialog');
  dialog.className = 'logout-confirmation';
  dialog.setAttribute('aria-labelledby', 'logout-confirmation-title');
  dialog.setAttribute('aria-describedby', 'logout-confirmation-message');
  dialog.innerHTML = `
    <div class="logout-confirmation__content">
      <h2 id="logout-confirmation-title">Log out?</h2>
      <p id="logout-confirmation-message">Are you sure you want to log out?</p>
      <div class="logout-confirmation__actions">
        <button type="button" data-logout-cancel>Cancel</button>
        <button type="button" data-logout-confirm>Log out</button>
      </div>
    </div>`;
  document.body.append(dialog);

  let logoutUrl = '';
  let trigger = null;

  const closeDialog = () => {
    if (dialog.open) dialog.close();
  };

  dialog.querySelector('[data-logout-cancel]').addEventListener('click', closeDialog);
  dialog.querySelector('[data-logout-confirm]').addEventListener('click', () => {
    if (!logoutUrl) return;
    window.location.assign(logoutUrl);
  });
  dialog.addEventListener('close', () => {
    logoutUrl = '';
    if (trigger && trigger.isConnected) trigger.focus();
    trigger = null;
  });
  dialog.addEventListener('click', event => {
    if (event.target === dialog) closeDialog();
  });

  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const source = event.target instanceof Element ? event.target.closest('a[href], [data-logout-href]') : null;
    if (!source || (source instanceof HTMLAnchorElement && (source.target === '_blank' || source.hasAttribute('download')))) return;

    const rawUrl = source instanceof HTMLAnchorElement ? source.href : source.getAttribute('data-logout-href');
    if (!rawUrl) return;

    const destination = new URL(rawUrl, window.location.href);
    if (destination.origin !== window.location.origin || !/\/logout\.php$/i.test(destination.pathname)) return;

    event.preventDefault();
    logoutUrl = destination.href;
    trigger = source;
    dialog.showModal();
    dialog.querySelector('[data-logout-cancel]').focus();
  });
})();
