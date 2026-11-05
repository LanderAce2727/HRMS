<?php
session_start();

/* =========================================
   DATABASE CONNECTION & CONFIGURATION
========================================= */

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = db();
} catch (Exception $e) {
    // Fallback PDO connection if db() helper is not present
    $host = "localhost";
    $dbname = "smart_hrms";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $ex) {
        die("Database connection failed. Please check your database settings.");
    }
}

/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION['user_id'])) {
    die("Please log in first before accessing this page.");
}

$userId = (int) $_SESSION['user_id'];

/* =========================================
   HELPER FUNCTION
========================================= */

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* =========================================
   VERIFY STUDENT ACCOUNT
========================================= */

$stmt = $pdo->prepare("
    SELECT
        s.id AS student_id,
        s.first_name,
        s.middle_name,
        s.last_name,
        s.student_number,
        s.section_id,
        s.program,
        s.year_level,
        sec.section_name,
        sec.semester,
        sec.academic_year
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN roles r ON u.role_id = r.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.user_id = ?
      AND s.status = 'active'
      AND s.verification_status = 'verified'
      AND u.is_active = 1
      AND r.role_name = 'student'
    LIMIT 1
");

$stmt->execute([$userId]);
$student = $stmt->fetch();

if (!$student) {
    die("Access denied. Only verified and active students can access faculty evaluation.");
}

$studentId = (int)$student['student_id'];
$sectionId = (int)$student['section_id'];

/* =========================================
   CSRF TOKEN
========================================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================================
   FLASH MESSAGE
========================================= */

$message = "";
$messageType = "success";

if (isset($_SESSION['evaluation_message'])) {
    $message = $_SESSION['evaluation_message'];
    $messageType = $_SESSION['evaluation_type'] ?? "success";

    unset($_SESSION['evaluation_message']);
    unset($_SESSION['evaluation_type']);
}

/* =========================================
   SUBMIT EVALUATION
========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Check CSRF token
        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            throw new Exception("Invalid request. Please refresh the page.");
        }

        $scheduleId = filter_input(
            INPUT_POST,
            'schedule_id',
            FILTER_VALIDATE_INT
        );

        $rating = filter_input(
            INPUT_POST,
            'rating',
            FILTER_VALIDATE_INT
        );

        $comment = trim($_POST['comment'] ?? '');

        if (!$scheduleId || !$rating || $rating < 1 || $rating > 5) {
            throw new Exception("Please select a valid rating.");
        }

        if (mb_strlen($comment) > 2000) {
            throw new Exception("Your comment must not exceed 2,000 characters.");
        }

        /*
         * Verify that the selected schedule belongs
         * to the logged-in student's section.
         */

        $stmt = $pdo->prepare("
            SELECT
                ts.id AS schedule_id,
                ts.instructor_id,
                ts.subject_id,
                ts.semester,
                ts.academic_year,
                e.first_name,
                e.last_name,
                sub.subject_name
            FROM teaching_schedules ts
            INNER JOIN employees e
                ON ts.instructor_id = e.id
            INNER JOIN subjects sub
                ON ts.subject_id = sub.id
            WHERE ts.id = ?
              AND ts.section_id = ?
              AND ts.status = 'active'
              AND e.is_faculty = 1
              AND e.current_status = 'active'
              AND sub.status = 'active'
            LIMIT 1
        ");

        $stmt->execute([$scheduleId, $sectionId]);
        $schedule = $stmt->fetch();

        if (!$schedule) {
            throw new Exception("The selected faculty or subject is not assigned to your section.");
        }

        // Check if the student already evaluated this instructor and subject
        $stmt = $pdo->prepare("
            SELECT id
            FROM evaluations
            WHERE student_id = ?
              AND instructor_id = ?
              AND subject_id = ?
              AND semester = ?
              AND academic_year = ?
            LIMIT 1
        ");

        $stmt->execute([
            $studentId,
            $schedule['instructor_id'],
            $schedule['subject_id'],
            $schedule['semester'],
            $schedule['academic_year']
        ]);

        if ($stmt->fetch()) {
            throw new Exception("You have already evaluated this faculty member for this subject.");
        }

        // Save evaluation
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (
                student_id,
                instructor_id,
                subject_id,
                semester,
                academic_year,
                rating,
                comment,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'submitted')
        ");

        $stmt->execute([
            $studentId,
            $schedule['instructor_id'],
            $schedule['subject_id'],
            $schedule['semester'],
            $schedule['academic_year'],
            $rating,
            $comment !== '' ? $comment : null
        ]);

        $_SESSION['evaluation_message'] = "Your evaluation has been submitted successfully.";
        $_SESSION['evaluation_type'] = "success";

    } catch (PDOException $e) {

        // Handles duplicate entries from the database unique constraint
        if ($e->getCode() === '23000') {
            $_SESSION['evaluation_message'] =
                "You have already submitted an evaluation for this faculty member and subject.";
        } else {
            error_log($e->getMessage());
            $_SESSION['evaluation_message'] =
                "Something went wrong while saving your evaluation.";
        }

        $_SESSION['evaluation_type'] = "error";

    } catch (Exception $e) {

        $_SESSION['evaluation_message'] = $e->getMessage();
        $_SESSION['evaluation_type'] = "error";
    }

    header("Location: Student_Evaluation_Form.php");
    exit;
}

/* =========================================
   GET FACULTY AND SUBJECT SCHEDULES
========================================= */

$stmt = $pdo->prepare("
    SELECT
        ts.id AS schedule_id,
        ts.instructor_id,
        ts.subject_id,
        ts.day,
        ts.start_time,
        ts.end_time,
        ts.semester,
        ts.academic_year,

        e.first_name AS faculty_first_name,
        e.middle_name AS faculty_middle_name,
        e.last_name AS faculty_last_name,
        e.position_title,

        d.department_name,

        sub.subject_code,
        sub.subject_name,

        sec.section_name

    FROM teaching_schedules ts

    INNER JOIN employees e
        ON ts.instructor_id = e.id

    INNER JOIN departments d
        ON e.department_id = d.id

    INNER JOIN subjects sub
        ON ts.subject_id = sub.id

    INNER JOIN sections sec
        ON ts.section_id = sec.id

    WHERE ts.section_id = ?
      AND ts.status = 'active'
      AND e.is_faculty = 1
      AND e.current_status = 'active'
      AND sub.status = 'active'

    ORDER BY e.last_name ASC, sub.subject_code ASC
");

$stmt->execute([$sectionId]);
$schedules = $stmt->fetchAll();

/* =========================================
   GET PREVIOUS EVALUATIONS
========================================= */

$stmt = $pdo->prepare("
    SELECT
        instructor_id,
        subject_id,
        semester,
        academic_year
    FROM evaluations
    WHERE student_id = ?
");

$stmt->execute([$studentId]);
$previousEvaluations = $stmt->fetchAll();

$evaluated = [];

foreach ($previousEvaluations as $evaluation) {
    $key = $evaluation['instructor_id'] . "_" .
           $evaluation['subject_id'] . "_" .
           $evaluation['semester'] . "_" .
           $evaluation['academic_year'];

    $evaluated[$key] = true;
}

$studentName = trim(
    $student['first_name'] . " " .
    ($student['middle_name'] ? $student['middle_name'] . " " : "") .
    $student['last_name']
);

?>
<!doctype html>
<html lang="en" data-theme="light" data-ready="true">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Faculty Evaluation | DNSC Smart HRMS</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap');

:root{color-scheme:light;--bg:#f5f8fe;--surface:#fff;--panel:#fff;--fg:#142348;--ink:#142348;--muted:#5b6d8d;--line:#dce4f5;--border:#dce4f5;--accent:#326bfa;--blue:#326bfa;--cyan:#17b9e0;--accent-2:#17b9e0;--accent-ink:#fff;--glow:#326bfa22;--slot-bg:#edf3ff;--slot-line:#7ea2ff;--shadow:0 10px 30px #2144880d;--link:#2458cd}
:root[data-theme=dark]{color-scheme:dark;--bg:#0a1226;--surface:#111b36;--panel:#111b36;--fg:#eaf0ff;--ink:#eaf0ff;--muted:#a4b2d2;--line:#2b3b60;--border:#2b3b60;--accent:#89b1ff;--blue:#89b1ff;--cyan:#36cbe8;--accent-2:#36cbe8;--accent-ink:#07112b;--glow:#89b1ff22;--slot-bg:#192748;--slot-line:#567ab5;--shadow:0 10px 30px #0003;--link:#a5c5ff}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.6 'DM Sans','Segoe UI',sans-serif}button,input,select,textarea{font:inherit}a{color:var(--blue)}button,a,input,select,textarea{touch-action:manipulation}button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid var(--blue);outline-offset:3px}button{cursor:pointer}h1,h2,h3{font-family:Outfit,'Segoe UI',sans-serif;line-height:1.2}h1{font-size:clamp(27px,4vw,36px);margin:0 0 8px}h2{font-size:20px;margin:0 0 16px}p{color:var(--muted)}.demo-layout{display:flex;min-height:100vh}.demo-sidebar{width:248px;flex:none;padding:28px 15px;background:var(--panel);border-right:1px solid var(--line)}.demo-brand{display:flex;gap:12px;align-items:center;margin:0 10px 30px;font:700 21px Outfit,sans-serif}.demo-brand small{display:block;font:11px 'DM Sans',sans-serif;color:var(--muted);letter-spacing:.08em}.demo-logo{width:42px;height:42px;border:1px dashed var(--slot-line);border-radius:13px;background:var(--slot-bg);flex:none}.demo-sidebar nav{display:grid;gap:5px}.demo-sidebar nav a{padding:11px 13px;color:var(--muted);text-decoration:none;border-radius:11px;font-weight:600}.demo-sidebar nav a:hover,.demo-sidebar nav a[aria-current=page]{background:var(--slot-bg);color:var(--blue)}.demo-main{padding:28px 32px;min-width:0;flex:1;max-width:1600px}.demo-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:24px;padding:12px;border:1px solid var(--line);border-radius:14px;background:var(--panel);color:var(--ink);font:14px/1.5 'DM Sans',sans-serif}.demo-toolbar a,.demo-toolbar button,.demo-toolbar select,.demo-button{border:1px solid var(--line);background:var(--panel);color:var(--blue);padding:10px 13px;border-radius:10px;text-decoration:none;font-weight:600;display:inline-block}.demo-toolbar select{max-width:100%;min-width:0}.demo-toolbar strong{margin-right:auto}.demo-button.primary{background:#326bfa;color:white;border-color:#326bfa}.demo-heading{margin-bottom:24px}.demo-heading p{margin:0}.demo-panel{border:1px solid var(--line);background:var(--panel);border-radius:18px;padding:22px;margin-bottom:20px;box-shadow:var(--shadow)}.demo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));gap:18px}.demo-stat strong{display:block;font:700 30px Outfit,sans-serif;margin:8px 0}.demo-muted{color:var(--muted)}.demo-badge{display:inline-block;padding:4px 10px;border-radius:20px;color:var(--blue);background:var(--slot-bg);font-size:12px;font-weight:700}.demo-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.demo-table-wrap{overflow:auto}table{border-collapse:collapse;width:100%;white-space:nowrap;text-align:left}td,th{padding:13px 12px;border-bottom:1px solid var(--line)}th{font-size:12px;color:var(--muted);background:var(--bg)}.demo-form{display:grid;gap:16px;max-width:780px}.demo-form label{display:grid;gap:6px;font-weight:600}.demo-form input,.demo-form textarea,.demo-form select,.demo-search{width:100%;border:1px solid var(--line);background:var(--bg);color:var(--ink);padding:12px;border-radius:10px;font-size:16px}.demo-form textarea{min-height:100px}.demo-search{max-width:400px;margin-bottom:16px}.demo-clock{text-align:center;font-variant-numeric:tabular-nums}.demo-clock time{font:700 clamp(38px,7vw,76px) Outfit,sans-serif;color:var(--blue)}.demo-scanner{border:2px dashed var(--blue);background:var(--slot-bg);padding:28px;border-radius:18px;text-align:center}.demo-scanner input{max-width:430px}.demo-toast{position:fixed;bottom:20px;right:20px;left:20px;max-width:600px;margin-left:auto;background:var(--ink);color:var(--bg);border-radius:12px;padding:16px;z-index:100}.demo-toast:empty{display:none}.demo-progress{height:10px;border-radius:20px;background:var(--bg);overflow:hidden}.demo-progress span{display:block;height:100%;background:#326bfa}.demo-menu{display:none}.demo-toolbar~.shell{height:auto}.sidebar,.btn,.quick button,.toolbar input,.toolbar select{background:var(--panel)!important;color:var(--ink)!important}.notice,.nav button.active,.nav button:hover,.metricicon,.eventicon,.avatar,.logo,.badgecount{background:var(--slot-bg)!important;color:var(--blue)!important;border-color:var(--line)!important}.nav button{color:var(--muted)!important}.btn.primary{background:#326bfa!important;color:#fff!important}.status{color:var(--ink);background:var(--slot-bg)}.preview-nav{display:none!important}
@media(max-width:760px){.demo-layout{display:block}.demo-sidebar{width:100%;padding:16px;border-right:0;border-bottom:1px solid var(--line)}.demo-sidebar .demo-brand{margin-bottom:12px}.demo-sidebar nav{display:none}.demo-sidebar.open nav{display:grid}.demo-menu{display:inline-block}.demo-main{padding:18px}.demo-toolbar{gap:8px}.demo-toolbar strong{width:100%}.demo-toolbar select{width:100%}.demo-panel{padding:17px}}
@media print{.demo-sidebar,.demo-toolbar,.demo-actions,.demo-toast{display:none}body,.demo-panel{background:white;color:black}.demo-main{padding:0}}

:root{--page:var(--bg);--sub:var(--muted);--soft:var(--slot-bg)}
html[data-theme="dark"]{--page:var(--bg);--surface:#111b36;--ink:#eaf0ff;--sub:var(--muted);--border:#2b3b60;--soft:var(--slot-bg);--blue:#89b1ff}
.side,.top,.panel{border-color:var(--line)}.side,.panel{background:var(--surface)}
.side .nav a.active{background:var(--slot-bg);color:var(--blue)}
.content .demo-toolbar{margin-bottom:24px}.demo-toolbar [data-logout]{margin-left:auto}
.login-attendance{margin-left:auto;display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:8px 14px;border:1px solid var(--line);border-radius:12px;background:var(--slot-bg);color:var(--blue);font-weight:600;text-decoration:none;white-space:nowrap}
.login-attendance:hover{border-color:var(--blue);text-decoration:none}
@media(max-width:420px){.left .top{flex-wrap:wrap;gap:8px}.login-attendance{padding:8px 10px}}

.hrms-header{display:flex;align-items:center;gap:18px;flex-wrap:wrap;padding:18px clamp(18px,3vw,36px);background:var(--panel);border-bottom:1px solid var(--line);color:var(--ink)}
.hrms-brand{display:grid;gap:3px;text-decoration:none;color:var(--ink);margin-right:auto;line-height:1.35}.hrms-brand strong{font:700 clamp(19px,2.5vw,24px) Outfit,'Segoe UI',sans-serif;letter-spacing:.025em}.hrms-brand span{font-size:12px;color:var(--muted)}
.hrms-public-nav{display:flex;gap:6px;flex-wrap:wrap}.hrms-public-nav a{padding:8px 10px;border-radius:9px;text-decoration:none;font-weight:600;color:var(--muted)}.hrms-public-nav a:hover,.hrms-public-nav a[aria-current=page]{color:var(--blue);background:var(--slot-bg)}
.hrms-header button{border:1px solid var(--line);border-radius:10px;padding:10px 13px;background:var(--panel);color:var(--blue);font-weight:600;min-height:44px}.hrms-menu{margin-left:auto;white-space:nowrap}
body .hrms-layout{--sidebar-width:236px;display:grid;grid-template-columns:var(--sidebar-width) minmax(0,1fr);min-height:calc(100vh - 90px);align-items:stretch;transition:grid-template-columns .25s ease}
body .hrms-layout.hrms-collapsed{--sidebar-width:0px}
body .hrms-layout>.hrms-sidebar{display:block;position:static;inset:auto;width:auto;min-width:0;overflow:hidden;padding:24px 14px;background:var(--panel);border-right:1px solid var(--line);box-shadow:none;transition:padding .25s ease,opacity .2s ease}
body .hrms-collapsed>.hrms-sidebar{padding-left:0;padding-right:0;opacity:0;border:0;visibility:hidden;transition:padding .25s ease,opacity .2s ease,visibility 0s .25s}
.hrms-sidebar> .brand,.hrms-sidebar> .demo-brand{display:none}.hrms-sidebar nav{display:grid;gap:5px;min-width:207px}.hrms-sidebar a{overflow-wrap:anywhere}.hrms-sidebar>p,.hrms-sidebar .side-note,.hrms-sidebar .sidebarfoot{min-width:190px}
body .hrms-layout>.main,body .hrms-layout>.demo-main{width:100%;min-width:0;max-width:1600px;margin:0 auto}.hrms-prototype{font-size:12px;color:var(--muted);background:var(--slot-bg);border:1px solid var(--line);border-radius:10px;padding:10px 14px;margin:0 0 22px;line-height:1.5}
.demo-panel{min-width:0}.demo-heading p{max-width:760px}.demo-form{width:100%}.demo-form input[type=file]{max-width:100%}.demo-button{min-height:44px}.hrms-hero{padding:clamp(22px,5vw,54px);margin-bottom:24px;background:var(--slot-bg);border:1px solid var(--line);border-radius:18px}.hrms-hero p{max-width:650px}.hrms-login{height:auto;padding:0}.hrms-login .shell{min-height:0;margin:26px auto;padding:0 16px}.hrms-login .hrms-prototype{margin:0 0 18px}.hrms-login .left>.top{flex-wrap:wrap}.hrms-login .tabs .tab{white-space:normal}
@media(max-width:1000px){.hrms-public-nav{order:3;width:100%}.hrms-header{gap:12px}.hrms-header #demoTheme{margin-left:auto}.hrms-header .hrms-menu{margin-left:0}}
@media(max-width:760px){body .hrms-layout{display:grid;grid-template-columns:minmax(0,1fr);min-height:0}body .hrms-layout>.hrms-sidebar{max-height:800px;padding:16px;border-right:0;border-bottom:1px solid var(--line);transition:max-height .25s ease,padding .25s ease,opacity .2s ease}body .hrms-collapsed>.hrms-sidebar{max-height:0;padding:0;border:0}.hrms-sidebar nav{display:grid}.hrms-header{padding:16px}.hrms-public-nav{gap:2px}.hrms-public-nav a{padding:8px;font-size:13px}.hrms-header .hrms-brand{width:100%;margin:0}.hrms-header #demoTheme{margin-left:0}.hrms-header .hrms-menu{margin-left:auto}body .hrms-layout>.main,body .hrms-layout>.demo-main{padding:18px 16px}.hrms-layout .content{padding:18px 0}.hrms-layout .top{padding:0 0 18px}.hrms-layout .mobile-brand{display:none}}
@media(prefers-reduced-motion:reduce){body .hrms-layout,body .hrms-layout>.hrms-sidebar{transition:none!important}}
@media print{.hrms-header,.hrms-prototype,.hrms-sidebar{display:none!important}body .hrms-layout{display:block}}
[hidden]{display:none!important}
@media(max-width:760px){.hrms-header{display:grid;grid-template-columns:minmax(0,1fr) auto}.hrms-header .hrms-brand{grid-column:1;grid-row:1;width:auto}.hrms-header .hrms-menu{grid-column:2;grid-row:1;margin:0}.hrms-header .hrms-public-nav{grid-column:1/-1;grid-row:2;width:auto}.hrms-header #demoTheme{grid-column:1/-1;grid-row:3;justify-self:start}.hrms-brand strong{overflow-wrap:normal}.hrms-brand span{font-size:11px}}

:root{--bg:#f3f7f4;--surface:#fff;--panel:#fff;--fg:#17372c;--ink:#17372c;--muted:#5b7066;--line:#d9e5de;--border:#d9e5de;--accent:#17613f;--blue:#17613f;--cyan:#3b9467;--accent-2:#3b9467;--accent-ink:#fff;--glow:#17613f22;--slot-bg:#e8f2eb;--slot-line:#8ab79c;--shadow:0 8px 28px #174b2e09;--link:#17613f;--page:var(--bg);--sub:var(--muted);--soft:var(--slot-bg)}
:root[data-theme=dark]{--bg:#0e1914;--surface:#16271e;--panel:#16271e;--fg:#edf5ef;--ink:#edf5ef;--muted:#aec4b6;--line:#30493a;--border:#30493a;--accent:#8dd3a7;--blue:#8dd3a7;--cyan:#9cd3b2;--accent-2:#9cd3b2;--accent-ink:#102b1b;--glow:#8dd3a722;--slot-bg:#203b2b;--slot-line:#537b60;--shadow:0 8px 28px #0002;--link:#a2e0b7}

body{background:var(--bg);color:var(--ink)}
.demo-button.primary{background:var(--accent);color:var(--accent-ink);border-color:var(--accent)}
.demo-button:disabled{opacity:.6;cursor:wait}
.hrms-brand strong{color:var(--blue)}
.hrms-header .icon-button{width:44px;height:44px;padding:0;display:grid;place-items:center;flex:none}
.icon-button svg{pointer-events:none}
.public-layout{max-width:1600px;margin:0 auto}
.preserve-lines{white-space:pre-wrap;overflow-wrap:anywhere}
.job-excerpt{display:-webkit-box;display:box;-webkit-line-clamp:3;line-clamp:3;-webkit-box-orient:vertical;box-orient:vertical;overflow:hidden}
.empty-state{padding:18px 0;color:var(--muted)}
.system-notice{margin:16px clamp(16px,3vw,36px);padding:14px 18px;border:1px solid var(--slot-line);border-radius:12px;background:var(--slot-bg);color:var(--ink)}
.system-notice.error{border-color:#be7575;background:#fff0ef;color:#8b2828}
:root[data-theme=dark] .system-notice.error{background:#422323;color:#ffd5d5}
.export-button{margin:0 12px 18px 0}.table-search{max-width:360px;margin-bottom:16px}
.account-name{font-size:12px;overflow-wrap:anywhere}
.demo-table-wrap td{vertical-align:top;max-width:360px;white-space:normal;overflow-wrap:anywhere}
td form{min-width:210px}.demo-form input[type=file]{max-width:100%}
.skip-link{position:fixed;left:16px;top:-100px;padding:12px;background:var(--panel);z-index:100;color:var(--blue);border:2px solid var(--blue)}.skip-link:focus{top:12px}
@media print{.hrms-header,.hrms-sidebar,.system-notice,form,.demo-button,.skip-link,.table-search{display:none!important}.hrms-layout{display:block!important}.demo-main{padding:0}.demo-table-wrap{overflow:visible}body{color:#000;background:#fff}}

header.hrms-header.has-sidebar-menu #menu-toggle{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;min-width:44px;min-height:44px;padding:0;margin:0!important;flex:0 0 44px;white-space:normal}
header.hrms-header.has-sidebar-menu #menu-toggle svg{display:block;pointer-events:none}
@media(max-width:760px){header.hrms-header.has-sidebar-menu{display:grid;grid-template-columns:44px minmax(0,1fr) 44px;gap:10px}header.hrms-header.has-sidebar-menu #menu-toggle{grid-column:1;grid-row:1}header.hrms-header.has-sidebar-menu .hrms-brand{grid-column:2;grid-row:1;width:auto;margin:0;min-width:0}header.hrms-header.has-sidebar-menu #theme-mode{grid-column:3;grid-row:1;margin:0}header.hrms-header.has-sidebar-menu .hrms-public-nav{grid-column:1/-1;grid-row:2;width:100%}header.hrms-header.has-sidebar-menu .hrms-brand strong{font-size:clamp(15px,4vw,19px)}header.hrms-header.has-sidebar-menu .hrms-brand span{font-size:10px}}

.sidebar-panel-view>.sidebar-panel-active{margin-top:0}
.hrms-sidebar nav a[aria-current=location]{background:var(--slot-bg);color:var(--blue);box-shadow:inset 3px 0 0 var(--blue)}
@media(prefers-reduced-motion:reduce){.sidebar-panel-view{scroll-behavior:auto}}

/* Evaluation Form Custom UI Extensions */
.student-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.student-info-item {
    font-size: 13px;
}

.student-info-item strong {
    color: var(--ink);
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
}

.subject-info-box {
    background: var(--slot-bg);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 14px 16px;
    margin: 14px 0 18px;
}

.subject-code-tag {
    font-weight: 700;
    color: var(--blue);
    font-size: 15px;
}

.rating-options-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.rating-options-group label {
    cursor: pointer;
    border: 1px solid var(--line);
    background: var(--bg);
    border-radius: 10px;
    padding: 10px 18px;
    transition: all 0.2s ease;
    text-align: center;
    font-weight: 700;
    min-width: 58px;
}

.rating-options-group input[type="radio"] {
    display: none;
}

.rating-options-group label:has(input:checked) {
    background: var(--blue);
    border-color: var(--blue);
    color: #fff;
    box-shadow: 0 4px 12px var(--glow);
}

.submitted-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--slot-bg);
    color: var(--blue);
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid var(--line);
}
</style>
<link rel="stylesheet" href="../assets/navigation.css">
<link rel="stylesheet" href="../assets/light-theme.css?v=20261028-unified-employee">
</head>
<body class="sidebar-shell">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="hrms-header has-sidebar-menu">
    <button type="button" class="hrms-menu icon-button" id="menu-toggle" aria-controls="portal-sidebar" aria-expanded="true" aria-label="Collapse navigation" title="Collapse navigation">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <a class="hrms-brand" href="../index.php"><strong>DNSC SMART HRMS</strong><span>Human Resource Management System</span></a>
    <nav class="hrms-public-nav" aria-label="Main navigation">
        <a href="../index.php">Home</a>
        <a href="../job-vacancies.php">Job Vacancies</a>
        <a href="../login.php">Logout</a>
    </nav>
    <button type="button" class="icon-button" id="theme-mode" aria-label="Switch to dark mode"></button>
</header>

<div id="notice" class="system-notice" role="status" tabindex="-1" <?= $message ? '' : 'hidden' ?>>
    <?= clean($message) ?>
</div>

<div class="demo-layout hrms-layout">
    <aside class="demo-sidebar hrms-sidebar" id="portal-sidebar">
        <p class="account-name">Student Pages</p>
        <nav aria-label="Section navigation">
            <a href="Student_Verification_Status.php">Enrollment Status</a>
            <a href="Student_Faculty_Selection.php">Faculty Evaluation</a>
            <a href="Student_Submission_Confirmation.php">My Submissions</a>
        </nav>
    </aside>

    <main class="demo-main" id="main-content">
        <header class="demo-heading">
            <h1>Faculty Evaluation</h1>
            <p>Evaluate your instructors based on your learning experience.</p>
        </header>

        <!-- STUDENT INFORMATION PANEL -->
        <section class="demo-panel">
            <h2>Student Information</h2>
            <div class="student-info-grid">
                <div class="student-info-item">
                    <strong>Student Name</strong>
                    <?= clean($studentName) ?>
                </div>
                <div class="student-info-item">
                    <strong>Student Number</strong>
                    <?= clean($student['student_number']) ?>
                </div>
                <div class="student-info-item">
                    <strong>Program</strong>
                    <?= clean($student['program']) ?>
                </div>
                <div class="student-info-item">
                    <strong>Year Level</strong>
                    <?= clean($student['year_level']) ?>
                </div>
                <div class="student-info-item">
                    <strong>Section</strong>
                    <?= clean($student['section_name'] ?? 'Not assigned') ?>
                </div>
                <div class="student-info-item">
                    <strong>Academic Year</strong>
                    <?= clean($student['academic_year'] ?? 'Not available') ?>
                </div>
            </div>
        </section>

        <!-- INSTRUCTIONS -->
        <section class="demo-panel">
            <h2>Evaluation Instructions</h2>
            <p>Select a rating from 1 to 5 for each instructor and subject. You may also leave a comment about your experience.</p>
            <p class="demo-muted" style="font-size:12px; margin-top:6px;">
                <strong>Rating Scale:</strong> 1 - Poor | 2 - Fair | 3 - Good | 4 - Very Good | 5 - Excellent
            </p>
        </section>

        <!-- FACULTY EVALUATION LIST / CARDS -->
        <?php if (empty($schedules)): ?>
            <section class="demo-panel">
                <h2>No Faculty Schedules Found</h2>
                <p class="empty-state">There are currently no active teaching schedules assigned to your section. Please contact your administrator.</p>
            </section>
        <?php else: ?>
            <?php foreach ($schedules as $schedule): ?>
                <?php
                    $evaluationKey =
                        $schedule['instructor_id'] . "_" .
                        $schedule['subject_id'] . "_" .
                        $schedule['semester'] . "_" .
                        $schedule['academic_year'];

                    $alreadyEvaluated = isset($evaluated[$evaluationKey]);

                    $facultyName = trim(
                        $schedule['faculty_first_name'] . " " .
                        ($schedule['faculty_middle_name']
                            ? $schedule['faculty_middle_name'] . " "
                            : "") .
                        $schedule['faculty_last_name']
                    );
                ?>
                <section class="demo-panel">
                    <h2><?= clean($facultyName) ?></h2>
                    <p class="demo-muted" style="margin-top:-10px;">
                        <?= clean($schedule['position_title']) ?> &bull; <?= clean($schedule['department_name']) ?>
                    </p>

                    <div class="subject-info-box">
                        <div class="subject-code-tag"><?= clean($schedule['subject_code']) ?></div>
                        <div style="font-weight:600; font-size:14px; margin-top:2px;"><?= clean($schedule['subject_name']) ?></div>
                        <div class="demo-muted" style="font-size:12px; margin-top:4px;">
                            <?= clean($schedule['section_name']) ?> &bull;
                            <?= clean($schedule['academic_year']) ?> &bull;
                            <?= $schedule['semester'] == 1 ? '1st Semester' : '2nd Semester' ?>
                            <br>
                            <?= clean($schedule['day']) ?> &bull;
                            <?= clean(date('h:i A', strtotime($schedule['start_time']))) ?> -
                            <?= clean(date('h:i A', strtotime($schedule['end_time']))) ?>
                        </div>
                    </div>

                    <?php if ($alreadyEvaluated): ?>
                        <div class="submitted-status-badge">
                            ✓ Evaluation Submitted
                        </div>
                    <?php else: ?>
                        <form method="POST" action="Student_Evaluation_Form.php" class="demo-form">
                            <input type="hidden" name="csrf_token" value="<?= clean($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="schedule_id" value="<?= (int)$schedule['schedule_id'] ?>">

                            <div>
                                <label style="font-weight:700; display:block; margin-bottom:6px;">
                                    How would you rate this instructor?
                                </label>
                                <div class="rating-options-group">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label>
                                            <input type="radio" name="rating" value="<?= $i ?>" required>
                                            <span><?= $i ?></span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <span class="demo-muted" style="font-size:11px; display:block; margin-top:6px;">1 = Poor, 5 = Excellent</span>
                            </div>

                            <label style="margin-top:10px;">
                                Comments or Suggestions (Optional)
                                <textarea name="comment" rows="3" maxlength="2000" placeholder="Write your feedback here..."></textarea>
                            </label>

                            <div style="margin-top:10px;">
                                <button type="submit" class="demo-button primary" onclick="return confirm('Are you sure you want to submit this evaluation?');">
                                    Submit Evaluation
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<script>
(() => {
'use strict';
const sun='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
const moon='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 13.1A9 9 0 0 1 10.9 3 9 9 0 1 0 21 13.1Z"/></svg>';
const message=document.getElementById('notice');
const show=()=>{if(message){message.hidden=false;message.textContent='This action is currently unavailable.';message.focus();}};
function theme(value){document.documentElement.dataset.theme=value;document.querySelectorAll('#theme-mode').forEach(b=>{b.innerHTML=value==='dark'?sun:moon;b.title=value==='dark'?'Switch to light mode':'Switch to dark mode';b.setAttribute('aria-label',b.title);b.setAttribute('aria-pressed',String(value==='dark'));});try{localStorage.setItem('dnsc-theme',value);}catch(e){}}
let saved='light';try{saved=localStorage.getItem('dnsc-theme')==='dark'?'dark':'light';}catch(e){}theme(saved);
document.querySelectorAll('#theme-mode').forEach(b=>b.onclick=()=>theme(document.documentElement.dataset.theme==='dark'?'light':'dark'));
const menu=document.getElementById('menu-toggle'),side=document.getElementById('portal-sidebar'),layout=document.querySelector('.hrms-layout');
if(menu&&side&&layout){
 const media=matchMedia('(max-width:760px)');
 const stateKey=()=>media.matches?'dnsc-sidebar-mobile':'dnsc-sidebar-desktop';
 const remembered=()=>{try{const saved=sessionStorage.getItem(stateKey());if(saved!==null)return saved==='open';}catch(e){}return !media.matches;};
 let open=remembered();
 const update=()=>{layout.classList.toggle('hrms-collapsed',!open);side.inert=!open;menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Collapse navigation':'Expand navigation');menu.title=open?'Collapse navigation':'Expand navigation';try{sessionStorage.setItem(stateKey(),open?'open':'closed');}catch(e){}};
 menu.onclick=()=>{open=!open;update();};
 media.addEventListener('change',()=>{open=remembered();update();});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&open){open=false;update();menu.focus();}});update();
}

if(side&&layout){
 const content=document.getElementById('main-content');
 if(content){
  const reduced=()=>matchMedia('(prefers-reduced-motion: reduce)').matches;
  const top=()=>{content.scrollTop=0;content.scrollLeft=0;};
  let animation=null,navigating=false;
  const reveal=()=>{if(animation)animation.cancel();if(!reduced()&&content.animate)animation=content.animate([{opacity:0},{opacity:1}],{duration:220,easing:'cubic-bezier(.2,.8,.2,1)'});};
  const focusContent=()=>{content.setAttribute('tabindex','-1');focusContent(true);};
  try{history.scrollRestoration='manual';}catch(e){}
  const links=[...side.querySelectorAll('nav a[href]')];
  for(const link of links){
   if(link.getAttribute('href').startsWith('#'))continue;
   link.addEventListener('click',event=>{
    if(event.defaultPrevented||event.button>0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey||link.target==='_blank'||link.hasAttribute('download'))return;
    const target=new URL(link.href,location.href);
    if(target.protocol!==location.protocol||target.origin!==location.origin||(!target.pathname.toLowerCase().endsWith('.html')&&!target.pathname.toLowerCase().endsWith('.php')))return;
    event.preventDefault();if(navigating)return;
    const samePage=target.pathname===location.pathname&&target.search===location.search;
    if(samePage){top();reveal();return;}
    navigating=true;
    if(reduced()||!content.animate){location.href=target.href;return;}
    if(animation)animation.cancel();
    animation=content.animate([{opacity:1},{opacity:0}],{duration:130,easing:'ease-out',fill:'forwards'});
    let completed=false;const navigate=()=>{if(completed)return;completed=true;location.href=target.href;};
    animation.onfinish=navigate;
    setTimeout(navigate,190);
   });
  }
  window.addEventListener('pageshow',event=>{top();if(event.persisted){navigating=false;if(animation)animation.cancel();reveal();}});
 }
}

document.querySelectorAll('[data-reveal]').forEach(b=>b.onclick=()=>{const input=document.getElementById(b.dataset.reveal);if(input){input.type=input.type==='password'?'text':'password';b.textContent=input.type==='password'?'Show':'Hide';b.setAttribute('aria-pressed',String(input.type==='text'));}});
document.querySelectorAll('.demo-table-wrap').forEach(w=>{if(!w.querySelector('table'))return;const search=document.createElement('input');search.type='search';search.className='demo-search table-search';search.placeholder='Search this table';search.setAttribute('aria-label','Search this table');search.oninput=()=>w.querySelectorAll('tbody tr').forEach(r=>r.hidden=!r.textContent.toLowerCase().includes(search.value.toLowerCase()));w.before(search);});
document.querySelectorAll('[data-print]').forEach(b=>b.onclick=()=>window.print());
const tick=()=>{const clock=document.getElementById('live-clock'),date=document.getElementById('live-date');if(clock)clock.textContent=new Intl.DateTimeFormat('en-PH',{timeZone:'Asia/Manila',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date());if(date)date.textContent=new Intl.DateTimeFormat('en-PH',{timeZone:'Asia/Manila',dateStyle:'full'}).format(new Date());};tick();if(document.getElementById('live-clock'))setInterval(tick,1000);
})();
</script>
</body>
</html>