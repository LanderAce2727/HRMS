# DNSC Smart HRMS frontend UI/UX flow

The current implementation is HTML5, CSS3, and browser JavaScript only. No PHP, database, server API, OAuth, or real credential verification is used.

## New pages and shared frontend files

- login.html: original single-card appearance with email/username, password, Sign In, and Google continuation. It contains no registration, signup, account creation, or forgot-password links. Demo-account details accompany the credentials section.
- applicant-verification.html: mock Google profile, optional PRC via Not Applicable, required-field validation, and success notification before Job Vacancies.
- job-vacancies.html: four sample Instructor I positions in IT, Mathematics, English, and Business Administration; each card contains all requested details and a selected-job link. The listings and deadlines are explicitly illustrative, not actual DNSC openings.
- job-application.html: selected position and department, personal/professional fields, four document controls, validation, and a same-page submitted confirmation. No redirect follows submission.
- faculty-dashboard.html: faculty profile, weekly schedule, subject cards, classroom assignments, mock attendance/history, and announcements.
- assets/ui-model.js: sample records, predefined account matching, validation, mock state persistence, HR decisions, and faculty assignment rules.
- assets/ui-flow.js: page-specific DOM behavior and navigation. No network request or real authentication is performed.
- assets/ui-flow.css: responsive DNSC green-and-white extension of the original shared design.

## Reused pages

The existing hr-admin/HR_Admin_Applicant_Management.html was enhanced instead of adding a duplicate applicant-management.html. It supports details, decisions, faculty registration, department/subject/room/schedule assignment, filters, and sample reset. Department selection and schedule validation are enforced; overlapping faculty times and already-booked sample classrooms are rejected.

Campus Sign In.html, DNSC_Sign_In_Single_Card.html, authentication/Authentication_Login.html, and authentication/Authentication_Login_Single_Card.html use the same updated sign-in interface and shared controller. They do not contain signup/registration options.

DNSC_HR_Dashboard.html and hr-admin/HR_Admin_Dashboard.html keep their original main content/components. Their palette and relevant links were updated. index.html keeps its existing layout with entry links into the new flow and the requested blue palette. Unrelated student, employee, attendance, registration, and legacy applicant pages were preserved.

## Navigation and behavior

Continue with Google -> applicant-verification.html -> job-vacancies.html -> job-application.html?job=<known sample key> -> submitted confirmation on the same page.

Faculty demo sign-in -> faculty-dashboard.html.

HR demo sign-in -> existing HR dashboard -> existing Applicant Management -> approve application -> Register as Faculty -> optional View Faculty Dashboard link.

Login matches only predefined demonstration account strings. It does not authenticate against any service and does not store entered passwords. The Google control sets a sample profile and never requests a Google password or contacts Google.

The mock state is namespaced in browser localStorage. Role changes and assignments affect only that mock state. No account is activated, no message is sent, and no document contents are read/uploaded. The application stores document names and sizes only for the HR detail view. All user-provided strings rendered into HTML are escaped.

Form fieldsets begin disabled until their JavaScript submit handlers are installed. Without JavaScript, no credential form is enabled for native submission. PRC Not Applicable disables and removes the required license-number condition. Application attachment checks enforce required documents, supported extensions, and the 5 MB per-file limit.

## Demo credentials

Faculty: faculty.demo or faculty.demo@dnsc.example / FacultyDemo123!

HR Admin: hr.demo or hr.demo@dnsc.example / HrDemo123!

## Deployment and verification

The dist/ folder and static-site.zip include all 38 HTML pages plus the three shared flow assets. Upload their contents while preserving the folder structure. No database setup is needed.

21 isolated model/controller checks passed. They cover the two credential routes, invalid credentials, mock Google profile, PRC/required-field checks, four vacancy links, selected-position propagation, file validation, same-page confirmation, HR approval/rejection, faculty assignment restrictions, schedule/room conflicts, role transitions, faculty-dashboard data, and reset behavior. Local file references, inline JavaScript syntax, duplicate IDs, login link restrictions, and deployment-copy consistency were also checked.

A real browser visual review could not run: computer use returned no connected browsers and an unavailable native pipe. No claim of visual browser verification is made.

Backend files and databases were not touched. Prior local recovery copies remain under backups/before-static-20261027/; frontend recovery for this update is under backups/before-ui-flow-20261027/. Neither is part of the deployment package.

The latest presentation update restores the DNSC forest-green/white palette and removes visible demo/sample badges, notices, and account hints. Field labels and validation remain intact. The implementation still uses the same frontend-only mock flows; removing presentation labels does not create real authentication, verification, or uploads.

Sidebar selection now uses a short fade/slide transition. Faculty same-page menu sections behave as exclusive content panels in the same content area instead of stacked scroll targets. Selection updates the URL hash and active state without native anchor scrolling; mobile navigation closes automatically. Separate HTML menu destinations retain their files, fade out/in, and start at the top. Reduced-motion preferences and modified/new-tab clicks are respected.

Applicant self-registration and its navigation links have been removed. The old registration URL now opens the existing sign-in interface for compatibility. All 25 sidebar pages load assets/navigation.css for a fixed header/menu frame, consistent Segoe UI fallbacks, line heights, menu rows, and content headings. Only the main content area scrolls. Menu open/closed state is retained per viewport through sessionStorage; selecting a tab no longer closes or shifts the menu. Content transitions use opacity only, with no vertical movement. Deployment copies and the ZIP include the same rules.
