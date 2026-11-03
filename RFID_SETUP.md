# RFID scanner attendance

Open `Attendance.html` directly in your browser or through Live Server.
`Attendance.php` serves the same page if you use XAMPP. No database, card
registration, PHP API, or network connection is needed for scanning.

Keep the page active, connect the 13.56 MHz keyboard-type USB reader, and tap
any card or tag. The first tap for that UID shows green **TIME IN**; the second
shows orange **TIME OUT**. Further taps continue alternating. Each UID tracks
its own state, so another person's card starts with TIME IN.

Readers with an Enter suffix submit immediately. Readers without Enter submit
350 milliseconds after typing the UID finishes. Click the RFID input if another
app or control has taken keyboard focus.

The small popup shows the detected UID and time. Names and employee details
will require a future data source; no personal information is invented. The
popup closes after 6 seconds, while the most recently scanned card's log stays
visible. Records exist only in page memory and reset on refresh or closing.

Run `node tests/attendance-kiosk.test.cjs` to verify scan handling.

For collaborators preparing real registered-card attendance, see
`RFID_DATABASE_GUIDE.php`: Part 1 contains disabled PHP code; Part 2 contains
separate step-by-step Bisaya instructions for the database and activation.
The matching DATABASE CLIENT and DATABASE SCAN HOOK blocks in
`assets/attendance-kiosk.js` are also commented out. Enable all three blocks
together only after the database is ready. The current demo is unchanged.
