# DNSC Smart HRMS testing setup

Open Campus Sign In.html or authentication/Authentication_Login.html. Both use the supplied single-card login design. Email and password may be left blank. Applicant opens job listings, HR Admin opens the dashboard, and Instructor opens the supplied faculty attendance screen.

Portal sidebars provide navigation without a workspace switcher. Attendance.html is accessible directly from the top-right Attendance link on all login pages. Attendance editing controls exist only on the HR attendance page; this is a frontend UI separation, not server authorization. All requested pages and the alternate login remain connected. The shared account toolbar includes Log out, which returns to login without creating or clearing any real authenticated session. There is no backend authentication or database integration. Record changes remain in page memory; only theme preference is stored. System-facing wording is used throughout the interface for testing.

The downloaded originals were read without modification. Workspace copies of those designs are saved in templates/login.source and templates/attendance.source. Before this refresh, the replaced login, dashboard, and attendance files were copied to backups/before-login-refresh as .bak files.

The faculty screen retains its clock in Asia/Manila, RFID keyboard input, and scan log. Faculty cannot edit log entries; the HR attendance screen provides correction controls. A scan records an entry in memory only.

Shared styles and behavior live in assets/demo.css and assets/demo.js. scripts/build_demo.py regenerates pages and then runs scripts/customize_system.py to retain the supplied designs and system wording. No framework or new dependency was installed.

All 33 active HTML pages passed parsing, local-reference, duplicate-ID, and inline JavaScript syntax checks during this update. Browser visual verification remains unavailable because no browser is connected.
