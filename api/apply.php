<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['success' => false, 'message' => 'Use POST to submit an application.']);
}

$savedFile = null;

try {
    // ---------- read input ----------
    $email      = strtolower(trim((string)($_POST['email'] ?? '')));
    $password   = (string)($_POST['password'] ?? '');
    $fullName   = trim((string)($_POST['full_name'] ?? ''));
    $age        = (int)($_POST['age'] ?? 0);
    $phoneRaw   = trim((string)($_POST['phone'] ?? ''));
    $position   = trim((string)($_POST['position'] ?? ''));
    $education  = trim((string)($_POST['education'] ?? ''));
    $yearsRaw   = (string)($_POST['years'] ?? '');
    $experience = trim((string)($_POST['experience'] ?? ''));
    $consent    = ($_POST['consent'] ?? '') === '1';

    $phone = preg_replace('/[^\d+]/', '', $phoneRaw);
    $phoneDigits = preg_replace('/\D/', '', $phone);

    $allowedPositions = ['Instructor (full-time)', 'Instructor (part-time)'];
    $allowedEducation = ["Bachelor's degree", "Master's degree", 'Doctorate'];

    // ---------- validate (mirrors the form) ----------
    if (!preg_match('/^[^\s@]+@gmail\.com$/i', $email) || strlen($email) > 100) {
        respond(422, ['success' => false, 'step' => 1, 'message' => 'Enter a Gmail address that ends in @gmail.com.']);
    }
    if (strlen($password) < 8) {
        respond(422, ['success' => false, 'step' => 1, 'message' => 'Use a password with at least 8 characters.']);
    }
    if ($fullName === '' || mb_strlen($fullName) > 150 ||
        $age < 18 || $age > 70 ||
        strlen($phoneDigits) < 10 || strlen($phone) > 20 ||
        !in_array($position, $allowedPositions, true) ||
        !in_array($education, $allowedEducation, true) ||
        $yearsRaw === '' || !ctype_digit($yearsRaw) || (int)$yearsRaw > 50) {
        respond(422, ['success' => false, 'step' => 2, 'message' => 'Fill in every field. Age must be 18 to 70 and the mobile number needs 10 digits.']);
    }
    $years = (int)$yearsRaw;

    if (!$consent) {
        respond(422, ['success' => false, 'message' => 'Tick the consent box to submit.']);
    }

    // ---------- resume ----------
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
        respond(422, ['success' => false, 'message' => 'Upload your resume as a PDF to continue.']);
    }
    $file = $_FILES['resume'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $tooBig = in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
        respond(422, ['success' => false, 'message' => $tooBig ? 'That PDF is over 5 MB. Compress it and try again.' : 'Resume upload failed. Try again.']);
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        respond(422, ['success' => false, 'message' => 'That PDF is over 5 MB. Compress it and try again.']);
    }
    $head = (string)file_get_contents($file['tmp_name'], false, null, 0, 5);
    if ($head !== '%PDF-') {
        respond(422, ['success' => false, 'message' => 'Resume must be a PDF file.']);
    }

    // ---------- duplicate email ----------
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        respond(409, ['success' => false, 'step' => 1, 'message' => 'An account with this Gmail already exists.']);
    }

    // ---------- applicant role ----------
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'applicant' LIMIT 1");
    $stmt->execute();
    $roleId = $stmt->fetchColumn();
    if (!$roleId) {
        respond(500, ['success' => false, 'message' => 'Applicant role is missing in the database.']);
    }

    // ---------- unique username ----------
    $base = preg_replace('/[^a-z0-9._]/', '', strtolower(explode('@', $email)[0]));
    $base = substr($base !== '' ? $base : 'applicant', 0, 40);
    $username = $base;
    $check = $pdo->prepare('SELECT 1 FROM users WHERE username = :u LIMIT 1');
    $check->execute(['u' => $username]);
    if ($check->fetch()) {
        $username = $base . '_' . bin2hex(random_bytes(3));
    }

    // ---------- save resume file ----------
    $dir = dirname(__DIR__) . '/uploads/resumes';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        respond(500, ['success' => false, 'message' => 'Could not create the resume folder.']);
    }
    $storedName = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
    $savedFile = $dir . '/' . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $savedFile)) {
        $savedFile = null;
        respond(500, ['success' => false, 'message' => 'Could not save the resume file.']);
    }
    $resumePath = 'uploads/resumes/' . $storedName;

    // ---------- insert user + application ----------
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO users (branch_id, role_id, username, email, password_hash, is_active)
         VALUES (NULL, :role_id, :username, :email, :hash, 1)'
    );
    $stmt->execute([
        'role_id'  => (int)$roleId,
        'username' => $username,
        'email'    => $email,
        'hash'     => password_hash($password, PASSWORD_DEFAULT),
    ]);
    $userId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        "INSERT INTO applications
            (vacancy_id, applicant_user_id, full_name, email, contact_number,
             age, position_applied, highest_education, years_experience,
             experience_details, resume_file_path, status)
         VALUES
            (NULL, :uid, :name, :email, :phone,
             :age, :pos, :edu, :years,
             :exp, :resume, 'submitted')"
    );
    $stmt->execute([
        'uid'    => $userId,
        'name'   => $fullName,
        'email'  => $email,
        'phone'  => $phone,
        'age'    => $age,
        'pos'    => $position,
        'edu'    => $education,
        'years'  => $years,
        'exp'    => $experience !== '' ? $experience : null,
        'resume' => $resumePath,
    ]);
    $applicationId = (int)$pdo->lastInsertId();

    $pdo->commit();

    respond(201, [
        'success' => true,
        'message' => 'Application received.',
        'application_id' => $applicationId,
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($savedFile && is_file($savedFile)) {
        @unlink($savedFile);
    }
    if ($e->getCode() === '23000') { // duplicate email/username race
        respond(409, ['success' => false, 'step' => 1, 'message' => 'An account with this Gmail already exists.']);
    }
    error_log('HRMS apply error: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'A server error occurred. Make sure you ran the SQL update.']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($savedFile && is_file($savedFile)) {
        @unlink($savedFile);
    }
    error_log('HRMS apply error: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'A server error occurred while submitting.']);
}