(() => {
  'use strict';
  const input = document.getElementById('rfid-input');
  const form = document.getElementById('rfid-form');
  const feedback = document.getElementById('scan-feedback');
  const card = document.getElementById('tap-card');
  // Temporary state for this open page only. No requests or database access.
  const attendance = new Map();
  let hideTimer, scanTimer;
  const formatTime = (value, showSeconds = true) => value ? new Intl.DateTimeFormat('en-PH', {
    timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', ...(showSeconds ? { second: '2-digit' } : {}), hour12: true
  }).format(value) : '\u2014';
  const formatDate = value => new Intl.DateTimeFormat('en-PH', {
    timeZone: 'Asia/Manila', year: 'numeric', month: 'short', day: 'numeric'
  }).format(value);

  // ========================================================================
  // DATABASE CLIENT
  // ========================================================================
  let databaseBusy = false;
  let databaseToken = '';
  let noticeTimer;
  const databaseEndpoint = new URL(
    /\/dist\/Attendance\.(html|php)$/.test(location.pathname)
      ? '../Attendance.php' : 'Attendance.php', document.baseURI);

  async function databaseRequest(action, options = {}) {
    const url = new URL(databaseEndpoint);
    url.searchParams.set('action', action);
    const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options });
    let data;
    try { data = await response.json(); } catch (error) {
      throw new Error('RFID service unavailable. Check PHP setup and activate the guide code.');
    }
    if (!response.ok || !data.ok) throw new Error(data.message || 'Scan could not be saved.');
    return data;
  }

  function renderAttendanceLogs(logs) {
    const rows = document.getElementById('attendance-rows');
    rows.replaceChildren();
    for (const log of logs) {
      const timestamp = value => value ? new Date(value.replace(' ', 'T') + '+08:00') : null;
      const values = [
        log.employee_name || '',
        log.work_date,
        formatTime(timestamp(log.check_in_time)),
        formatTime(timestamp(log.check_out_time)),
        log.attendance_status || log.status || ''
      ];
      const row = document.createElement('tr');
      for (const value of values) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      }
      rows.append(row);
    }
  }

  async function loadTodayAttendance() {
    if (databaseBusy) return;
    try {
      if (!databaseToken) databaseToken = (await databaseRequest('session')).token;
      const data = await databaseRequest('today', {
        headers: { 'X-RFID-Token': databaseToken }
      });
      renderAttendanceLogs(data.logs);
      document.getElementById('log-description').textContent = 'Today\'s attendance records. Updated just now.';
    } catch (error) {
      showError(error.message || 'Attendance records could not be loaded. Please try again.');
    }
  }

  function showError(text) {
    const notice = document.getElementById('notice');
    if (!notice) return;
    notice.className = 'system-notice error';
    notice.textContent = text;
    notice.hidden = false;
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => { notice.hidden = true; }, 5000);
  }

  // >>> RFID SCANNER CONNECTION: sends the typed UID to Attendance.php. <<<
  async function recordRegisteredRfid(uid) {
    if (databaseBusy) {
      feedback.textContent = 'Please wait for the current scan, then tap again.';
      return;
    }
    databaseBusy = true;
    card.hidden = true;
    document.getElementById('log-description').textContent = 'Checking registered card...';
    document.getElementById('scanner-state').textContent = 'Saving...';
    feedback.className = 'scan-feedback';
    try {
      if (!databaseToken) databaseToken = (await databaseRequest('session')).token;
      const data = await databaseRequest('scan', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-RFID-Token': databaseToken },
        body: JSON.stringify({ uid })
      });
      const employee = data.employee;
      const recordedAt = new Date(data.recorded_at);
      card.dataset.event = data.event === 'TIME OUT' ? 'out' : 'in';
      document.getElementById('tap-event').textContent = data.event;
      document.getElementById('tap-name').textContent = employee.name;
      document.getElementById('tap-number').textContent = employee.number + ' / ' + employee.department;
      document.getElementById('tap-initials').textContent = Array.from(employee.name)[0].toLocaleUpperCase();
      document.getElementById('tap-time').textContent = formatTime(recordedAt, false);
      document.getElementById('tap-date').textContent = formatDate(recordedAt);
      document.getElementById('tap-note').textContent = data.duplicate
        ? `Duplicate tap ignored. Please wait ${data.duplicate_wait_seconds || 1} second(s) before tapping again.`
        : 'Attendance saved successfully.';
      card.hidden = false;
      clearTimeout(hideTimer);
      hideTimer = setTimeout(() => { card.hidden = true; }, 6000);
      document.getElementById('log-description').textContent = employee.name + ' / ' + employee.number;
      await loadTodayAttendance();
      feedback.className = data.duplicate ? 'scan-feedback error' : 'scan-feedback success';
      feedback.textContent = data.duplicate
        ? `Duplicate tap ignored. Wait ${data.duplicate_wait_seconds || 1} second(s) before tapping again.`
        : data.event + ' saved.';
    } catch (error) {
      card.hidden = true;
      feedback.className = 'scan-feedback error';
      feedback.textContent = error.message;
      showError(error.message || 'Attendance could not be saved. Please try again.');
    } finally {
      databaseBusy = false;
      document.getElementById('scanner-state').textContent = 'Ready to scan';
      input.focus({ preventScroll: true });
    }
  }
  // ======================== END DATABASE CLIENT ============================

  function scan(event) {
    event.preventDefault();
    clearTimeout(scanTimer);
    const uid = input.value.trim();
    input.value = '';
    if (!uid) return;
    recordRegisteredRfid(uid);
    return;
    // ===================== END DATABASE SCAN HOOK =========================
    const now = new Date();
    const logs = attendance.get(uid) || [];
    let latest = logs[0];
    const timeOut = latest && !latest.out;
    if (timeOut) {
      latest.out = now;
    } else {
      latest = { in: now, out: null };
      logs.unshift(latest);
      logs.length = Math.min(logs.length, 30);
    }
    attendance.set(uid, logs);
    const eventName = timeOut ? 'TIME OUT' : 'TIME IN';
    card.dataset.event = timeOut ? 'out' : 'in';
    document.getElementById('tap-event').textContent = eventName;
    const userName = (card.dataset.userName || '').trim() || 'RFID User';
    document.getElementById('tap-name').textContent = userName;
    document.getElementById('tap-number').textContent = 'UID: ' + uid;
    document.getElementById('tap-initials').textContent = Array.from(userName)[0].toLocaleUpperCase();
    document.getElementById('tap-time').textContent = formatTime(now, false);
    document.getElementById('tap-date').textContent = formatDate(now);
    document.getElementById('tap-note').textContent = 'Recorded for this page session only.';
    card.hidden = false;
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => { card.hidden = true; }, 6000);

    document.getElementById('log-description').textContent = 'UID: ' + uid;
    const rows = document.getElementById('attendance-rows');
    rows.replaceChildren();
    for (const log of logs) {
      const row = document.createElement('tr');
      for (const value of [formatDate(log.in), formatTime(log.in), formatTime(log.out), log.out ? 'TIME OUT' : 'TIME IN']) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      }
      rows.append(row);
    }
    feedback.textContent = eventName + ' recorded. Ready for the next tap.';
    input.focus({ preventScroll: true });
  }

  form.addEventListener('submit', scan);
  // Accept readers that append Enter and readers that send only the UID.
  input.addEventListener('input', () => {
    clearTimeout(scanTimer);
    if (input.value) scanTimer = setTimeout(() => scan({ preventDefault() {} }), 350);
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('a,button,input,select,textarea')) input.focus({ preventScroll: true });
  });
  window.addEventListener('focus', () => input.focus({ preventScroll: true }));
  document.getElementById('tap-dismiss').addEventListener('click', () => {
    card.hidden = true;
    input.focus({ preventScroll: true });
  });
  document.getElementById('scanner-state').textContent = 'Ready to scan';
  feedback.textContent = 'Waiting for your RFID card or tag...';
  const attendanceTable = document.querySelector('#attendance-rows').closest('table');
  attendanceTable.querySelector('thead tr').replaceChildren(
    ...['Employee', 'Date', 'Time in', 'Time out', 'Status'].map(label => {
      const th = document.createElement('th');
      th.scope = 'col';
      th.textContent = label;
      return th;
    })
  );
  document.querySelectorAll('#attendance-rows').forEach(() => loadTodayAttendance());
  window.setInterval(loadTodayAttendance, 5000);
  input.focus({ preventScroll: true });
})();
