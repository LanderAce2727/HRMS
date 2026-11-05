<?php
session_start();

require_once __DIR__ . '/../config/db.php';

/* =========================================
   STUDENT SESSION VALIDATION
========================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id']) ||
    (int) $_SESSION['role_id'] !== 4
) {
    header('Location: ../login.php');
    exit;
}

/* =========================================
   DATABASE CONNECTION
========================================= */

$pdo = db();

$user_id = (int) $_SESSION['user_id'];

/* =========================================
   ESCAPE OUTPUT
========================================= */

function e($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/* =========================================
   GET STUDENT INFORMATION
========================================= */

$sql = "
    SELECT
        s.id,
        s.student_number,
        s.first_name,
        s.middle_name,
        s.last_name,
        s.program,
        s.year_level,
        s.enrollment_date,
        s.status,
        s.verification_status,
        s.review_notes,
        s.reviewed_at,
        sec.section_code,
        sec.section_name,
        sec.semester,
        sec.academic_year
    FROM students s
    LEFT JOIN sections sec
        ON sec.id = s.section_id
    WHERE s.user_id = :user_id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'user_id' => $user_id
]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================================
   STUDENT NAME
========================================= */

$studentName = '';

if ($student) {
    $studentName = trim(
        $student['first_name'] . ' ' .
        ($student['middle_name']
            ? $student['middle_name'] . ' '
            : '') .
        $student['last_name']
    );
}

/* =========================================
   VERIFICATION STATUS
========================================= */

$verification = $student['verification_status'] ?? 'pending';

switch ($verification) {

    case 'verified':
        $statusLabel = 'Verified';
        break;

    case 'rejected':
        $statusLabel = 'Rejected';
        break;

    default:
        $statusLabel = 'Pending Review';
        break;
}

/* =========================================
   ENROLLMENT STATUS
========================================= */

$enrollmentStatus = $student['status'] ?? 'pending';

?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Enrollment Status | DNSC Smart HRMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">

    <style>

        /* =========================================
           THEME VARIABLES
        ========================================= */

        :root {
            color-scheme: light;

            --bg: #f3f7f4;
            --surface: #ffffff;
            --panel: #ffffff;

            --ink: #17372c;
            --muted: #5b7066;

            --line: #d9e5de;

            --blue: #17613f;
            --accent: #17613f;

            --slot-bg: #e8f2eb;
            --slot-line: #8ab79c;

            --shadow: 0 8px 28px #174b2e09;
        }

        html[data-theme="dark"] {

            color-scheme: dark;

            --bg: #0e1914;
            --surface: #16271e;
            --panel: #16271e;

            --ink: #edf5ef;
            --muted: #aec4b6;

            --line: #30493a;

            --blue: #8dd3a7;
            --accent: #8dd3a7;

            --slot-bg: #203b2b;
            --slot-line: #537b60;

            --shadow: 0 8px 28px #0002;
        }

        /* =========================================
           GLOBAL STYLES
        ========================================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        a {
            color: var(--blue);
        }

        button {
            cursor: pointer;
        }

        button:focus-visible,
        a:focus-visible {
            outline: 3px solid var(--blue);
            outline-offset: 3px;
        }

        h1,
        h2,
        h3 {
            font-family: 'Outfit', sans-serif;
            line-height: 1.2;
        }

        h1 {
            font-size: clamp(27px, 4vw, 36px);
            margin: 0 0 8px;
        }

        h2 {
            font-size: 20px;
            margin: 0 0 16px;
        }

        p {
            color: var(--muted);
        }

        [hidden] {
            display: none !important;
        }

        /* =========================================
           HEADER
        ========================================= */

        .hrms-header {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;

            padding: 18px clamp(18px, 3vw, 36px);

            background: var(--panel);
            border-bottom: 1px solid var(--line);
        }

        .hrms-menu,
        .icon-button {
            width: 44px;
            height: 44px;
            min-width: 44px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid var(--line);
            border-radius: 10px;

            background: var(--panel);
            color: var(--blue);
        }

        .hrms-brand {
            display: grid;
            gap: 3px;

            text-decoration: none;
            color: var(--ink);

            margin-right: auto;
            line-height: 1.35;
        }

        .hrms-brand strong {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--blue);
        }

        .hrms-brand span {
            font-size: 12px;
            color: var(--muted);
        }

        .hrms-public-nav {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .hrms-public-nav a {
            padding: 8px 10px;
            border-radius: 9px;

            text-decoration: none;
            font-weight: 600;

            color: var(--muted);
        }

        .hrms-public-nav a:hover {
            color: var(--blue);
            background: var(--slot-bg);
        }

        /* =========================================
           PAGE LAYOUT
        ========================================= */

        .hrms-layout {
            display: grid;
            grid-template-columns: 236px minmax(0, 1fr);

            min-height: calc(100vh - 90px);

            transition: grid-template-columns .25s ease;
        }

        .hrms-layout.hrms-collapsed {
            grid-template-columns: 0 minmax(0, 1fr);
        }

        /* =========================================
           SIDEBAR
        ========================================= */

        .hrms-sidebar {
            min-width: 0;
            overflow: hidden;

            padding: 24px 14px;

            background: var(--panel);
            border-right: 1px solid var(--line);

            transition:
                padding .25s ease,
                opacity .2s ease;
        }

        .hrms-collapsed .hrms-sidebar {
            padding-left: 0;
            padding-right: 0;

            opacity: 0;
            visibility: hidden;
        }

        .account-name {
            font-size: 12px;
            font-weight: 700;
            margin: 0 10px 18px;
        }

        .hrms-sidebar nav {
            display: grid;
            gap: 5px;

            min-width: 207px;
        }

        .hrms-sidebar nav a {
            display: block;

            padding: 11px 13px;

            color: var(--muted);
            text-decoration: none;

            border-radius: 11px;
            font-weight: 600;
        }

        .hrms-sidebar nav a:hover,
        .hrms-sidebar nav a[aria-current="page"] {
            background: var(--slot-bg);
            color: var(--blue);
        }

        /* =========================================
           MAIN CONTENT
        ========================================= */

        .demo-main {
            width: 100%;
            min-width: 0;
            max-width: 1600px;

            margin: 0 auto;
            padding: 28px 32px;
        }

        .demo-heading {
            margin-bottom: 24px;
        }

        .demo-heading p {
            margin: 0;
            max-width: 760px;
        }

        /* =========================================
           PANELS
        ========================================= */

        .demo-panel {
            min-width: 0;

            padding: 22px;
            margin-bottom: 20px;

            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;

            box-shadow: var(--shadow);
        }

        .demo-grid {
            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(min(100%, 260px), 1fr));

            gap: 18px;
        }

        .demo-muted {
            color: var(--muted);
        }

        .demo-badge {
            display: inline-block;

            padding: 5px 12px;

            border-radius: 20px;

            color: var(--blue);
            background: var(--slot-bg);

            font-size: 12px;
            font-weight: 700;
        }

        /* =========================================
           TABLE
        ========================================= */

        .demo-table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 13px 12px;
            border-bottom: 1px solid var(--line);
        }

        th {
            font-size: 12px;
            color: var(--muted);
            background: var(--bg);
        }

        td {
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        /* =========================================
           BUTTONS
        ========================================= */

        .demo-button {
            display: inline-block;

            padding: 10px 16px;

            border: 1px solid var(--line);
            border-radius: 10px;

            background: var(--panel);
            color: var(--blue);

            text-decoration: none;
            font-weight: 700;
        }

        .demo-button.primary {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .demo-button:hover {
            opacity: .85;
        }

        /* =========================================
           EMPTY STATE
        ========================================= */

        .empty-state {
            padding: 18px 0;
            color: var(--muted);
        }

        /* =========================================
           NOTICE
        ========================================= */

        .system-notice {
            margin: 16px clamp(16px, 3vw, 36px);
            padding: 14px 18px;

            border: 1px solid var(--slot-line);
            border-radius: 12px;

            background: var(--slot-bg);
            color: var(--ink);
        }

        /* =========================================
           RESPONSIVE DESIGN
        ========================================= */

        @media (max-width: 760px) {

            .hrms-header {
                display: grid;
                grid-template-columns: 44px minmax(0, 1fr) 44px;
                gap: 10px;
            }

            .hrms-menu {
                grid-column: 1;
                grid-row: 1;
            }

            .hrms-brand {
                grid-column: 2;
                grid-row: 1;
                margin: 0;
                min-width: 0;
            }

            .hrms-brand strong {
                font-size: 16px;
            }

            .hrms-brand span {
                font-size: 10px;
            }

            .hrms-header #theme-mode {
                grid-column: 3;
                grid-row: 1;
            }

            .hrms-public-nav {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .hrms-layout {
                display: block;
                min-height: 0;
            }

            .hrms-layout.hrms-collapsed {
                display: block;
            }

            .hrms-sidebar {
                padding: 16px;
                border-right: 0;
                border-bottom: 1px solid var(--line);
                max-height: 400px;
            }

            .hrms-collapsed .hrms-sidebar {
                max-height: 0;
                padding: 0;
                border: 0;
            }

            .hrms-sidebar nav {
                min-width: 0;
            }

            .demo-main {
                padding: 18px 16px;
            }

            .demo-panel {
                padding: 17px;
            }

            .demo-table-wrap table {
                min-width: 650px;
            }
        }

        @media print {

            .hrms-header,
            .hrms-sidebar,
            .system-notice {
                display: none !important;
            }

            .hrms-layout {
                display: block;
            }

            .demo-main {
                padding: 0;
            }

            body {
                background: white;
                color: black;
            }
        }

    </style>

    <link rel="stylesheet" href="../assets/navigation.css">
    <link rel="stylesheet" href="../assets/light-theme.css?v=20261028-unified-employee">

</head>

<body>

   
    <!-- =========================================
         HEADER
    ========================================== -->

    <header class="hrms-header">

        <button
            type="button"
            class="hrms-menu"
            id="menu-toggle"
            aria-controls="portal-sidebar"
            aria-expanded="true"
            aria-label="Collapse navigation"
            title="Collapse navigation">

            <svg viewBox="0 0 24 24"
                 width="22"
                 height="22"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 aria-hidden="true">

                <path d="M4 6h16M4 12h16M4 18h16"/>

            </svg>

        </button>

        <a class="hrms-brand" href="../index.php">

            <strong>DNSC SMART HRMS</strong>

            <span>Human Resource Management System</span>

        </a>

        <nav class="hrms-public-nav" aria-label="Main navigation">

            <a href="../index.php">Home</a>

            <a href="../job-vacancies.php">Job Vacancies</a>

            <a href="../login.php">Logout</a>

        </nav>

        <button
            type="button"
            class="icon-button"
            id="theme-mode"
            aria-label="Switch to dark mode">

        </button>

    </header>

    <div
        id="notice"
        class="system-notice"
        role="status"
        tabindex="-1"
        hidden>
    </div>

    <!-- =========================================
         PAGE LAYOUT
    ========================================== -->

    <div class="hrms-layout">

        <!-- SIDEBAR -->

        <aside
            class="hrms-sidebar"
            id="portal-sidebar">

            <p class="account-name">Student Pages</p>

            <nav aria-label="Section navigation">

                <a
                    href="Student_Verification_Status.php"
                    aria-current="page">

                    Enrollment Status

                </a>

                <a href="Student_Faculty_Selection.php">

                    Faculty Evaluation

                </a>

                <a href="Student_Submission_Confirmation.php">

                    My Submissions

                </a>

            </nav>

        </aside>

        <!-- MAIN CONTENT -->

        <main class="demo-main" id="main-content">

            <header class="demo-heading">

                <h1>Enrollment Status</h1>

                <p>
                    HR verifies your enrollment before faculty evaluation
                    becomes available.
                </p>

            </header>

            <?php if (!$student): ?>

                <!-- NO ENROLLMENT RECORD -->

                <section class="demo-panel">

                    <h2>Enrollment Records</h2>

                    <div class="empty-state">

                        No enrollment record was found for your account.
                        Please contact the HR administrator.

                    </div>

                </section>

            <?php else: ?>

                <!-- STUDENT INFORMATION -->

                <section class="demo-panel">

                    <h2>Student Information</h2>

                    <div class="demo-grid">

                        <div>

                            <p class="demo-muted">Student Name</p>

                            <strong>
                                <?= e($studentName) ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Student Number</p>

                            <strong>
                                <?= e($student['student_number']) ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Program</p>

                            <strong>
                                <?= e($student['program']) ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Year Level</p>

                            <strong>
                                <?= e($student['year_level']) ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Section</p>

                            <strong>
                                <?= e($student['section_code'] ?? 'Not assigned') ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Academic Year</p>

                            <strong>
                                <?= e($student['academic_year'] ?? 'Not assigned') ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Semester</p>

                            <strong>
                                <?= e($student['semester'] ?? 'Not assigned') ?>
                            </strong>

                        </div>

                        <div>

                            <p class="demo-muted">Enrollment Date</p>

                            <strong>

                                <?= !empty($student['enrollment_date'])
                                    ? e(date(
                                        'F d, Y',
                                        strtotime($student['enrollment_date'])
                                    ))
                                    : 'Not available' ?>

                            </strong>

                        </div>

                    </div>

                </section>

                <!-- ENROLLMENT RECORDS -->

                <section class="demo-panel">

                    <h2>Enrollment Records</h2>

                    <div class="demo-table-wrap">

                        <table>

                            <thead>

                                <tr>

                                    <th scope="col">Student</th>

                                    <th scope="col">Student Number</th>

                                    <th scope="col">Program</th>

                                    <th scope="col">Year</th>

                                    <th scope="col">Status</th>

                                    <th scope="col">Review Notes</th>

                                    <th scope="col">Action</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td>
                                        <?= e($studentName) ?>
                                    </td>

                                    <td>
                                        <?= e($student['student_number']) ?>
                                    </td>

                                    <td>
                                        <?= e($student['program']) ?>
                                    </td>

                                    <td>
                                        <?= e($student['year_level']) ?>
                                    </td>

                                    <td>

                                        <span class="demo-badge">
                                            <?= e($statusLabel) ?>
                                        </span>

                                    </td>

                                    <td>

                                        <?= !empty($student['review_notes'])
                                            ? e($student['review_notes'])
                                            : 'No review notes.' ?>

                                    </td>

                                    <td>

                                        <?php if (
                                            $verification === 'verified' &&
                                            $enrollmentStatus === 'active'
                                        ): ?>

                                            <a
                                                href="Student_Faculty_Selection.php"
                                                class="demo-button primary">

                                                Continue

                                            </a>

                                        <?php elseif ($verification === 'rejected'): ?>

                                            <span class="demo-muted">
                                                Contact HR
                                            </span>

                                        <?php else: ?>

                                            <span class="demo-muted">
                                                Waiting for HR review
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>

                <!-- VERIFICATION DETAILS -->

                <section class="demo-panel">

                    <h2>Verification Details</h2>

                    <p>

                        Your enrollment verification status is:

                        <strong>
                            <?= e($statusLabel) ?>
                        </strong>

                    </p>

                    <?php if ($verification === 'pending'): ?>

                        <p>
                            Your enrollment is still waiting for HR verification.
                            Please wait for the HR administrator to review
                            your information.
                        </p>

                    <?php elseif ($verification === 'verified'): ?>

                        <p>
                            Your enrollment has been verified by HR.
                            You may now proceed to the Faculty Evaluation page
                            if your enrollment status is active.
                        </p>

                    <?php elseif ($verification === 'rejected'): ?>

                        <p>
                            Your enrollment verification was rejected.
                            Please read the HR review notes and contact
                            the HR administrator for assistance.
                        </p>

                    <?php endif; ?>

                    <p class="demo-muted">

                        Review date:

                        <?= !empty($student['reviewed_at'])
                            ? e(date(
                                'F d, Y h:i A',
                                strtotime($student['reviewed_at'])
                            ))
                            : 'Not yet reviewed' ?>

                    </p>

                </section>

            <?php endif; ?>

        </main>

    </div>

    <!-- =========================================
         JAVASCRIPT
    ========================================== -->

    <script>

        (() => {

            'use strict';

            /* =====================================
               THEME TOGGLE
            ===================================== */

            const themeButton = document.getElementById('theme-mode');

            const sun = `
                <svg viewBox="0 0 24 24"
                     width="22"
                     height="22"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     aria-hidden="true">

                    <circle cx="12" cy="12" r="4"/>

                    <path d="
                        M12 2v2
                        m0 16v2
                        M2 12h2
                        m16 0h2
                        M4.93 4.93l1.42 1.42
                        m11.3 11.3l1.42 1.42
                        M4.93 19.07l1.42-1.42
                        m11.3-11.3l1.42-1.42
                    "/>

                </svg>
            `;

            const moon = `
                <svg viewBox="0 0 24 24"
                     width="22"
                     height="22"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">

                    <path d="
                        M21 13.1
                        A9 9 0 0 1 10.9 3
                        A9 9 0 1 0 21 13.1Z
                    "/>

                </svg>
            `;

            function setTheme(theme) {

                document.documentElement.dataset.theme = theme;

                if (themeButton) {

                    themeButton.innerHTML =
                        theme === 'dark' ? sun : moon;

                    themeButton.title =
                        theme === 'dark'
                            ? 'Switch to light mode'
                            : 'Switch to dark mode';

                    themeButton.setAttribute(
                        'aria-label',
                        themeButton.title
                    );

                }

                try {

                    localStorage.setItem('dnsc-theme', theme);

                } catch (error) {

                    // Storage may be unavailable.

                }

            }

            let savedTheme = 'light';

            try {

                savedTheme =
                    localStorage.getItem('dnsc-theme') === 'dark'
                        ? 'dark'
                        : 'light';

            } catch (error) {

                savedTheme = 'light';

            }

            setTheme(savedTheme);

            if (themeButton) {

                themeButton.addEventListener('click', () => {

                    const currentTheme =
                        document.documentElement.dataset.theme;

                    setTheme(
                        currentTheme === 'dark'
                            ? 'light'
                            : 'dark'
                    );

                });

            }

            /* =====================================
               SIDEBAR TOGGLE
            ===================================== */

            const menuButton =
                document.getElementById('menu-toggle');

            const sidebar =
                document.getElementById('portal-sidebar');

            const layout =
                document.querySelector('.hrms-layout');

            if (menuButton && sidebar && layout) {

                const media = window.matchMedia(
                    '(max-width: 760px)'
                );

                function getSavedState() {

                    try {

                        const key = media.matches
                            ? 'dnsc-sidebar-mobile'
                            : 'dnsc-sidebar-desktop';

                        const saved = sessionStorage.getItem(key);

                        if (saved !== null) {
                            return saved === 'open';
                        }

                    } catch (error) {

                        // Use default state.

                    }

                    return !media.matches;

                }

                let isOpen = getSavedState();

                function updateSidebar() {

                    layout.classList.toggle(
                        'hrms-collapsed',
                        !isOpen
                    );

                    sidebar.inert = !isOpen;

                    menuButton.setAttribute(
                        'aria-expanded',
                        String(isOpen)
                    );

                    menuButton.setAttribute(
                        'aria-label',
                        isOpen
                            ? 'Collapse navigation'
                            : 'Expand navigation'
                    );

                    menuButton.title =
                        isOpen
                            ? 'Collapse navigation'
                            : 'Expand navigation';

                    try {

                        const key = media.matches
                            ? 'dnsc-sidebar-mobile'
                            : 'dnsc-sidebar-desktop';

                        sessionStorage.setItem(
                            key,
                            isOpen ? 'open' : 'closed'
                        );

                    } catch (error) {

                        // Sidebar still works without storage.

                    }

                }

                menuButton.addEventListener('click', () => {

                    isOpen = !isOpen;

                    updateSidebar();

                });

                updateSidebar();

            }

        })();

    </script>

</body>
</html>