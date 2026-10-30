# DNSC Smart HRMS - Frontend Walkthrough

Open **login.html** in your browser or through VS Code Live Server. No PHP, database, API, or Google OAuth setup is used.

## Demo accounts

| Role | Username (or demo email) | Password |
|---|---|---|
| Faculty | faculty.demo / faculty.demo@dnsc.example | FacultyDemo123! |
| HR Admin | hr.demo / hr.demo@dnsc.example | HrDemo123! |

These are predefined navigation examples, not real authentication. Do not enter real credentials.

## Applicant flow

1. In login.html, click Continue with Google.
2. Complete applicant-verification.html using the prefilled mock profile. Provide a PRC number or select Not Applicable.
3. Continue to job-vacancies.html and choose one of the four clearly labeled sample vacancies.
4. Complete job-application.html. Resume, application letter, and transcript are required; supporting credentials are optional. Each file must be at most 5 MB.
5. Submit to see the confirmation on that same application page. The flow ends there; no registration or faculty-dashboard redirect occurs.

## HR flow

Sign in with the HR demo account to open the preserved HR Admin Dashboard. Select Review applicants / Applicants to open the existing hr-admin/HR_Admin_Applicant_Management.html page.

- Search/filter mock applicants and open their details.
- Approve, reject, or mark an application under review.
- For an approved applicant, select Register as Faculty.
- Assign a department, subjects, sections, classrooms, days, and times. Additional schedules can be added or removed.
- Registration changes the role to Faculty in the mock state. The resulting View Faculty Dashboard link displays those assignments.
- Reset Sample Data restores the initial mock records.

A faculty registration is only a UI simulation; it does not activate an account or create new sign-in credentials. The predefined faculty demo login always remains available.

## Faculty flow

The faculty demo account opens faculty-dashboard.html with a sample faculty profile, weekly teaching schedule, assigned subjects/classrooms, mock attendance/history, and announcements. The HR preview link can display newly assigned mock faculty as well.

## Storage and deployment

Mock profile/application/assignment data is kept in browser localStorage under dnsc-ui-flow-v1. Passwords and document contents are never saved. Only filenames and sizes are retained for the application-detail UI. No files are uploaded. If the browser restricts file:// storage, VS Code Live Server gives the pages a consistent local origin for the walkthrough.

Upload the **contents of dist/** or extract **static-site.zip** into your hosting public folder. The new flow pages need the included assets/ui-model.js, assets/ui-flow.js, and assets/ui-flow.css files. Existing unrelated HTML pages remain in place.

## Checks

Run the frontend checks with Node.js:

```powershell
node tests/ui-flow.test.cjs
node tests/ui-navigation.test.cjs
```

The 21 model/controller checks cover demo routing, mock Google continuation, verification, selected-position propagation, document validation, confirmation without redirects, HR decisions, faculty registration, schedule conflicts, and faculty-dashboard rendering. These checks use isolated memory, not real accounts or project records. Visual browser review was unavailable because the computer-use browser/native connection was unavailable.

Shared DNSC green/white presentation extends the restored card/dashboard designs. No backend files or databases were modified for this update. Recovery copies of the affected frontend pages are kept locally in backups/before-ui-flow-20261027/.

The latest presentation update restores the DNSC forest-green/white palette and removes visible demo/sample badges, notices, and account hints. Field labels and validation remain intact. The implementation still uses the same frontend-only mock flows; removing presentation labels does not create real authentication, verification, or uploads.
