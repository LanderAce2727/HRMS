const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const code = fs.readFileSync(require('node:path').join(__dirname, '../assets/attendance-kiosk.js'), 'utf8');
function page() {
  const nodes = new Map(), timers = new Map();
  let timer = 0;
  const get = id => {
    if (!nodes.has(id)) nodes.set(id, { value: '', textContent: '', hidden: true, dataset: {}, events: {}, children: [],
      addEventListener(name, fn) { this.events[name] = fn; },
      replaceChildren() { this.children = []; }, append(child) { this.children.push(child); }, focus() {} });
    return nodes.get(id);
  };
  const context = { Intl, Date, Map,
    document: { getElementById: get, createElement: () => ({ textContent: '', children: [], append(child) { this.children.push(child); } }), addEventListener() {} },
    window: { addEventListener() {} },
    fetch() { throw new Error('Scanning must never make network requests'); },
    setTimeout(fn, ms) { timers.set(++timer, { fn, ms }); return timer; },
    clearTimeout(id) { timers.delete(id); }
  };
  vm.createContext(context);
  vm.runInContext(code, context);
  return { get, timers, scan(uid) {
    get('rfid-input').value = uid;
    get('rfid-form').events.submit({ preventDefault() {} });
  } };
}
const p = page(), get = p.get;
assert.equal(get('scanner-state').textContent, 'Ready to scan');
p.scan('00001234');
assert.equal(get('tap-card').hidden, false);
assert.equal(get('tap-card').dataset.event, 'in');
assert.equal(get('tap-event').textContent, 'TIME IN');
assert.equal(get('tap-number').textContent, 'UID: 00001234');
assert.equal(get('tap-name').textContent, 'RFID User');
assert.equal(get('tap-initials').textContent, 'R');
assert.match(get('tap-time').textContent, /^\d{2}:\d{2}\s[AP]M$/);
assert.ok(get('tap-date').textContent.length > 0);
assert.equal(get('rfid-input').value, '');
assert.equal(get('attendance-rows').children.length, 1);
assert.equal(get('attendance-rows').children[0].children[2].textContent, '\u2014');
console.log('PASS Any UID with leading zeros records green TIME IN without network or database');
p.scan('00001234');
assert.equal(get('tap-card').dataset.event, 'out');
assert.equal(get('tap-event').textContent, 'TIME OUT');
assert.match(get('tap-time').textContent, /^\d{2}:\d{2}\s[AP]M$/);
assert.notEqual(get('attendance-rows').children[0].children[2].textContent, '\u2014');
p.scan('00001234');
assert.equal(get('tap-event').textContent, 'TIME IN');
assert.equal(get('attendance-rows').children.length, 2);
console.log('PASS Repeated taps alternate TIME IN and TIME OUT, including a third tap');
p.scan('ANOTHER-TAG');
assert.equal(get('tap-event').textContent, 'TIME IN');
p.scan('00001234');
assert.equal(get('tap-event').textContent, 'TIME OUT');
console.log('PASS Each card tracks its own attendance state');
get('rfid-input').value = '<img src=x onerror=alert(1)>';
get('rfid-input').events.input();
const [timerId, pending] = [...p.timers].find(([, t]) => t.ms === 350);
p.timers.delete(timerId);
pending.fn();
assert.equal(get('tap-number').textContent, 'UID: <img src=x onerror=alert(1)>');
assert.equal(get('tap-event').textContent, 'TIME IN');
assert.equal([...p.timers.values()].filter(t => t.ms === 350).length, 0);
console.log('PASS UID without Enter submits automatically and displays as plain text');
get('rfid-input').value = 'WITH-ENTER';
get('rfid-input').events.input();
get('rfid-form').events.submit({ preventDefault() {} });
assert.equal([...p.timers.values()].filter(t => t.ms === 350).length, 0);
p.scan('');
assert.equal(get('tap-event').textContent, 'TIME IN');
console.log('PASS Enter cancels delayed submission and empty input does not toggle attendance');
const fresh = page();
fresh.scan('00001234');
assert.equal(fresh.get('tap-event').textContent, 'TIME IN');
assert.equal(fresh.get('attendance-rows').children.length, 1);
console.log('PASS Reloading starts a fresh attendance session');
fresh.get('tap-card').dataset.userName = 'Maria Santos';
fresh.scan('00001234');
assert.equal(fresh.get('tap-name').textContent, 'Maria Santos');
assert.equal(fresh.get('tap-initials').textContent, 'M');
console.log('PASS ID card shows the name initial, name, date, and hours/minutes for both tap events');
