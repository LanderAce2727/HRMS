# DNSC Smart HRMS

The application is running locally at **http://127.0.0.1:8085/index.html**.

Use the HTTP address above. Opening index.html with file:// or a static VS Code Live Server cannot run the PHP backend.

## Accounts and first use

All three existing database accounts and their passwords were preserved. Login determines the account's role from the database; users cannot choose a more privileged role in the browser.

1. Sign in with the existing HR administrator account.
2. Publish actual positions under Job Vacancies. The current database has no published vacancies; no fictional positions were inserted.
3. Review employee information, assign RFID cards, and set actual pay rates and their effective dates under Employee Records.
4. Set the work start time and open the actual academic evaluation period in Dashboard > System Settings. Leaving the evaluation period blank closes evaluation submissions.
5. Applicants can register, submit PDF resumes against open positions, view status, and see scheduled interviews. Students register separately and need HR enrollment verification before submitting faculty evaluations.

## Connected workflows

- Password-hashed login, database-backed roles, idle session expiry, POST logout, and password changes under Account.
- Applicant/student registration with unique email validation and automatic sign-in after successful database persistence.
- Vacancy publishing, editing, categories, and publication status.
- Application submission, duplicate-application prevention, private PDF storage/downloads, HR status decisions, and interview schedules.
- Employee appointment records, employee accounts, accepted-applicant appointments, pay rates, employment status histories, contact updates, and leave requests/reviews.
- RFID check-in and check-out against the signed-in employee's assigned card. Attendance is timed on the server in Philippine Standard Time. The second scan checks out; scans within one minute of check-in are rejected to prevent accidental duplicate scans.
- HR corrections to check-in/check-out with required reasons and an audit history.
- Daily-rate payslips from completed attendance records, entered deductions, duplicate/overlapping-period protection, CSV export, and printing. Rates must exist before the pay period starts; periods spanning a rate change must be split.
- Student verification, faculty evaluations with one response per faculty/period, and aggregated reports.
- Forest green/white DNSC styling, responsive sidebar, searchable tables, accessible sun/moon theme icons, and a saved theme preference.

All 33 separate page URLs remain. Existing HTML entry points now load the live shared interface in assets/hrms.js. The existing shared design classes and responsive styles are retained; assets/dnsc.css supplies the DNSC palette. Fake navigation, fictional dashboard figures, and simulated success messages are no longer active.

## Running again

Keep MySQL running in XAMPP, then run from the project directory:

```powershell
.\Start-HRMS.ps1
```

Or keep a terminal open with:

```powershell
php -d display_errors=Off -d upload_max_filesize=5M -d post_max_size=8M -S 127.0.0.1:8085 router.php
```

Start-HRMS.ps1 launches a hidden PHP process and records its PID in storage/server.pid. Server output and errors go to storage/server.log and storage/server-errors.log. A different local port can be selected with -Port.

The database defaults match the existing XAMPP setup: smart_hrms on 127.0.0.1:3306 with the existing local root connection. Override HRMS_DB_HOST, HRMS_DB_PORT, HRMS_DB_NAME, HRMS_DB_USER, and HRMS_DB_PASSWORD through environment variables for another installation.

For a fresh copy of the existing database, import the supplied SQL dump once and run:

```powershell
php scripts/install.php
```

The installer is CLI-only and adds the live application tables/columns without replacing users, passwords, or existing records. It was applied to this project's current database. The old frontend generators are retired so they cannot overwrite the live application.

Apache installations can use the included .htaccess and router.php with mod_rewrite and AllowOverride enabled. The local server is bound to 127.0.0.1 and is accessible only on this computer. Public hosting and HTTPS configuration are separate deployment work.

## Boundaries

- Email delivery, email-address verification, and emailed password recovery are not configured. Application decisions and interviews are visible in the applicant account. Forgotten-password recovery currently requires administrator assistance; the application does not pretend to send email.
- Payroll implements daily-rate calculations with HR-entered deductions. Automated statutory deductions, benefits, partial-day rules, and bank payments are not integrated.
- RFID requires a keyboard-style USB reader or entry of the assigned card number. Overnight shifts and multiple shifts in one day require additional attendance rules.
- Faculty evaluation uses the period explicitly configured by HR and verified student status. It is not integrated with a registrar/course enrollment system.
- This is a running local application, not a publicly deployed service. Browser visual/interaction review was unavailable because no browser/native UI connection was present. Syntax and startup checks were performed; no sample accounts, applications, attendance, or payroll records were created.

Private files, SQL dumps, uploaded resumes, session files, logs, scripts, and repository metadata are blocked by the application router. Resume downloads enforce account ownership or the HR role. CSRF validation, parameterized queries, active-user checks, login throttling, escaped rendering, and audited HR changes are implemented. Password changes and employment status changes invalidate existing sessions as appropriate.

## Original design restored

The original saved sign-in card, role selector, input styling, HR dashboard metric/chart/quick-action layout, and faculty clock/scanner layout are restored with DNSC green/white colors. The homepage uses the earlier compact heading and service panels. The other pages use their original shared card/form spacing.

Presentation rules were recovered from templates/login.source, templates/attendance.source, and the original committed HR dashboard into assets/original-design.css. Original preview scripts and fictional data were not reintroduced. assets/hrms.js still calls the existing live APIs for every account and record action. No backend or database changes were required for this design restoration.

The original sign-in design is used by Campus Sign In.html, DNSC_Sign_In_Single_Card.html, and the organized authentication login pages. Theme controls keep the sun/moon icons. Hard-refresh the browser (Ctrl+F5) to load the restored presentation.
