<?php
declare(strict_types=1);

// ============================================================================
// PART 1: ACTUAL PHP RFID CODE (DISABLED)
// Tangtanga LANG ang /* ug */ sa block sa ubos kung andam na ang database.
// ============================================================================

/*
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');

function rfidReply(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$db = null;
try {
    session_name('hrms_rfid_kiosk');
    session_set_cookie_params([
        'httponly' => true, 'samesite' => 'Strict', 'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    if (!session_start()) {
        throw new RuntimeException('PHP session folder is not writable.');
    }
    $action = $_GET['action'] ?? '';
    if ($action === 'session' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['rfid_token'] ??= bin2hex(random_bytes(32));
        rfidReply(200, ['ok' => true, 'token' => $_SESSION['rfid_token']]);
    }
    if ($action !== 'scan') {
        rfidReply(404, ['ok' => false, 'message' => 'Unknown RFID action.']);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        rfidReply(405, ['ok' => false, 'message' => 'POST a card scan.']);
    }
    if (!isset($_SESSION['rfid_token']) || !hash_equals($_SESSION['rfid_token'], $_SERVER['HTTP_X_RFID_TOKEN'] ?? '')) {
        rfidReply(403, ['ok' => false, 'message' => 'Reload the page to restart the scanner session.']);
    }
    session_write_close();
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 1024) {
        rfidReply(413, ['ok' => false, 'message' => 'Card data is too long.']);
    }
    $payload = json_decode(file_get_contents('php://input', false, null, 0, 1025), true, 8, JSON_THROW_ON_ERROR);
    $uid = is_array($payload) ? ($payload['uid'] ?? null) : null;
    if (!is_string($uid) || !preg_match('/\A[A-Za-z0-9:_-]{1,50}\z/D', $uid)) {
        rfidReply(422, ['ok' => false, 'message' => 'Invalid RFID UID.']);
    }

    // Set these environment variables on the PHP server; never put credentials in JS.
    $host = getenv('HRMS_DB_HOST') ?: '127.0.0.1';
    $port = getenv('HRMS_DB_PORT') ?: '3306';
    $name = getenv('HRMS_DB_NAME') ?: 'smart_hrms';
    $db = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
        getenv('HRMS_DB_USER') ?: 'root', getenv('HRMS_DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
    $db->exec("SET time_zone = '+08:00'");
    $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $date = $now->format('Y-m-d');
    $stamp = $now->format('Y-m-d H:i:s');

    $db->beginTransaction();
    // Lock one employee so simultaneous readers cannot create conflicting punches.
    $query = $db->prepare('SELECT e.id, e.employee_number, e.first_name, e.middle_name,
        e.last_name, e.current_status, e.position_title, u.is_active, u.branch_id,
        d.department_name FROM employees e JOIN users u ON u.id = e.user_id
        JOIN departments d ON d.id = e.department_id
        WHERE e.rfid_card_number = ? FOR UPDATE');
    $query->execute([$uid]);
    $employee = $query->fetch(PDO::FETCH_ASSOC);
    if (!$employee || $employee['current_status'] !== 'active' || !(int) $employee['is_active']) {
        throw new DomainException('Card not registered to an active employee. Contact HR.');
    }
    $query = $db->prepare('SELECT id, check_in_time, check_out_time FROM attendance_logs
        WHERE employee_id = ? AND work_date = ? FOR UPDATE');
    $query->execute([$employee['id'], $date]);
    $log = $query->fetch(PDO::FETCH_ASSOC);
    $event = 'TIME IN';
    $duplicate = false;
    if ($log) {
        $last = new DateTimeImmutable($log['check_out_time'] ?? $log['check_in_time'], new DateTimeZone('Asia/Manila'));
        if (abs($now->getTimestamp() - $last->getTimestamp()) < 5) {
            $duplicate = true;
            $event = $log['check_out_time'] ? 'TIME OUT' : 'TIME IN';
        } elseif ($log['check_out_time']) {
            throw new DomainException('Time in and time out are already complete for today.');
        } else {
            $event = 'TIME OUT';
            $query = $db->prepare('UPDATE attendance_logs SET check_out_time = ?, updated_at = ? WHERE id = ?');
            $query->execute([$stamp, $stamp, $log['id']]);
        }
    } else {
        $query = $db->prepare("INSERT INTO attendance_logs
            (employee_id, branch_id, work_date, check_in_time, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'present', ?, ?)");
        $query->execute([$employee['id'], $employee['branch_id'], $date, $stamp, $stamp, $stamp]);
    }
    $query = $db->prepare('SELECT work_date, check_in_time, check_out_time, status FROM attendance_logs
        WHERE employee_id = ? ORDER BY work_date DESC LIMIT 10');
    $query->execute([$employee['id']]);
    $logs = $query->fetchAll(PDO::FETCH_ASSOC);
    $db->commit();
    rfidReply(200, [
        'ok' => true, 'event' => $event, 'duplicate' => $duplicate,
        'recorded_at' => ($duplicate ? $last : $now)->format(DATE_ATOM),
        'employee' => [
            'name' => implode(' ', array_filter([$employee['first_name'], $employee['middle_name'], $employee['last_name']], static fn($v) => $v !== null && $v !== '')),
            'number' => $employee['employee_number'], 'position' => $employee['position_title'],
            'department' => $employee['department_name'],
        ],
        'logs' => $logs,
    ]);
} catch (Throwable $error) {
    if ($db instanceof PDO && $db->inTransaction()) $db->rollBack();
    if ($error instanceof DomainException) {
        rfidReply(409, ['ok' => false, 'message' => $error->getMessage()]);
    }
    if ($error instanceof JsonException) {
        rfidReply(400, ['ok' => false, 'message' => 'Invalid scan request.']);
    }
    error_log('RFID scan failed: ' . $error->getMessage());
    rfidReply(503, ['ok' => false, 'message' => 'Attendance could not be saved. Contact HR and try again.']);
}
*/


// ============================================================================
// PART 2: STEP-BY-STEP NGA GIYA SA BISAYA (DILI NI EXECUTABLE CODE)
// Ayaw tangtanga ang comments niini nga instructions.
// ============================================================================
//
// 1. AYAW SA I-ACTIVATE KUNG WALA PA ANG DATABASE.
//    Ang kasamtangang Attendance.html moandar gihapon isip browser-only demo.
//    Walay database nga gihimo o gi-connect niini nga guide samtang commented.
//
// 2. I-ANDAM ANG MYSQL / MARIADB UG PHP.
//    Sa XAMPP, paandara ang Apache ug MySQL. Kinahanglan enabled ang pdo_mysql.
//    Siguroha nga masulatan sa PHP ang session.save_path sa php.ini.
//    I-serve ang tibuok HRMS project; ayaw dist/ ra kay naa sa root kini nga file.
//    Gamita ang localhost URL, dili file:// o static Live Server sa database mode.
//
// 3. GAMITA ANG EXISTING SCHEMA SA "smart_hrms (v2.3).sql".
//    Kung bag-o ug walay sulod ang database, paghimo og smart_hrms sa phpMyAdmin
//    unya i-import ang SQL. Kung naa nay records, ayaw i-reimport ibabaw niini.
//    I-coordinate ang kulang nga columns/indexes sa inyong database collaborator.
//    Ayaw paghimo og laing RFID users table kung naa na ang employees table.
//
// 4. SIGUROHA NGA NAA KINING MGA TABLE UG FIELDS:
//    employees: id, user_id, employee_number, rfid_card_number VARCHAR(50),
//      first_name, middle_name, last_name, department_id, position_title,
//      current_status. Ang rfid_card_number kinahanglan UNIQUE; pwede NULL.
//    users: id, branch_id, is_active.
//    departments: id, department_name. branches: id.
//    attendance_logs: id AUTO_INCREMENT PRIMARY KEY, employee_id, branch_id,
//      work_date DATE, check_in_time TIMESTAMP, check_out_time TIMESTAMP NULL,
//      status, created_at, updated_at. Kinahanglan InnoDB ang mga tables.
//    Kinahanglan UNIQUE(employee_id, work_date) sa attendance_logs aron walay
//    duplicate nga adlaw. Ang supplied SQL naa na niini nga constraints ug FKs.
//    status kinahanglan modawat og 'present'. Walay schedule-based late check dinhi.
//
// 5. I-LINK ANG TINUOD NGA CARD SA EMPLOYEE.
//    Ablihi ang Notepad, i-tap ang card, ug kopyaha ang eksaktong UID.
//    Sa phpMyAdmin, ibutang sa employees.rfid_card_number sa SAKTONG employee.
//    Ayaw himoa og integer: pananglitan, ang '00001234' kinahanglan naay leading zeros.
//    Ayaw gamita ang parehas nga UID sa duha ka employee. Ayaw ibutang ang Enter
//    o newline. Ang format sa reader ug ang naka-save nga value kinahanglan pareho.
//    Dapat current_status='active' ug users.is_active=1; valid ang user_id,
//    department_id ug users.branch_id sa ilang related tables.
//
// 6. I-SET ANG DATABASE CREDENTIALS SA PHP SERVER.
//    HRMS_DB_HOST=127.0.0.1, HRMS_DB_PORT=3306, HRMS_DB_NAME=smart_hrms,
//    HRMS_DB_USER ug HRMS_DB_PASSWORD. Ilisi sa inyong aktuwal nga settings.
//    Pwede i-adjust ang PHP connection block sa taas sa inyong backend config.
//    Ayaw ibutang ang database password sa JavaScript o HTML, o i-commit sa Git.
//
// 7. I-ACTIVATE ANG PHP CODE.
//    Tangtanga ang opening /* ug closing */ SA PART 1 LANG. Ayaw apila kini nga
//    instructions. I-save kini nga file sa project root isip RFID_DATABASE_GUIDE.php.
//    Wala kini mag-CREATE TABLE: kinahanglan daan nang kompleto ang schema.
//
// 8. I-ACTIVATE ANG JAVASCRIPT CONNECTION.
//    Ablihi ang assets/attendance-kiosk.js. Pangitaa ang DATABASE CLIENT block
//    ug ang DATABASE SCAN HOOK block. Tangtanga ang /* ug */ sa DUHA ka blocks.
//    Ang hook adunay return aron dili modagan ang demo toggle human sa DB request.
//    Ayaw tangtanga ang return; kung wala, basin mo-display og success bisan failed.
//    Kung inyong gi-serve ang dist/Attendance.html, kopyaha ang updated JS ngadto
//    sa dist/assets/attendance-kiosk.js. I-refresh gamit Ctrl+F5.
//
// 9. SULAYI GAMIT ANG REGISTERED CARD.
//    I-open ang Attendance.php o Attendance.html pinaagi sa PHP server.
//    Unang tap: green TIME IN, tinuod nga ngalan, first initial, date ug HH:MM.
//    Hulata og labing menos 5 segundos, unya tap usab: orange TIME OUT.
//    Ang paspas nga repeat tap i-ignore aron dili aksidenteng ma-time out.
//    Ang unknown/inactive card kinahanglan mo-error, dili mo-create og employee.
//    I-refresh ug i-check ang attendance_logs: kinahanglan naa gihapon ang record.
//    Sulayi usab laing card; kinahanglan kaugalingong employee record ang mausab.
//
// 10. SABTA ANG DAILY RULE SA EXISTING SCHEMA.
//     Usa ra ka TIME IN / TIME OUT pair matag employee kada adlaw (Asia/Manila).
//     Kung kompleto na, dili na i-overwrite. Sa sunod adlaw bag-ong TIME IN.
//     Kung daghang shifts/breaks ang gusto, kinahanglan una usbon ang schema ug
//     handler sa inyong database collaborator. Ayaw tangtanga lang ang unique key.
//     Kung wala maka-time out kagahapon, ipa-correct sa HR ang daang record.
//
// 11. PARA SA ACTUAL DEPLOYMENT.
//     Shared attendance kiosk kini, dili employee dashboard login. Ibutang sa
//     controlled terminal/network; gamita ang HTTPS kung deployed na. Ang session
//     token anti-CSRF, dili authentication sa reader. Kung public kiosk access ang
//     inyong setup, ipa-integrate ang existing login/kiosk authorization sa backend.
//     Ang 13.56 MHz frequency dili API: keyboard input ang atong gamiton, dili COM.
//     Ayaw i-activate ang duha ka lain-laing scan handler sa parehas nga input.
