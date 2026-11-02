<?php
declare(strict_types=1);

/**
 * Shared helpers for the applicant pages
 * (applicant-verification.php, job-vacancies.php, job-application.php).
 *
 * Usage (pages in the project root):
 *   require_once __DIR__ . '/config/applicant_auth.php';
 *   $user = require_applicant();
 *
 * Session keys come from authentication/google-callback.php:
 *   user_id, role_id, email, name, picture
 */

require_once __DIR__ . '/db.php';

const APPLICANT_ROLE_ID = 3;
const EDUCATION_LEVELS  = ['Bachelor’s Degree', 'Master’s Degree', 'Doctorate', 'Other'];

/** HTML-escape for output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/**
 * Only signed-in applicants (role 3) get through.
 * Staff are sent to their own dashboard; everyone else to the login page.
 * Returns ['id' => int, 'email' => string, 'name' => string].
 */
function require_applicant(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Never cache an authenticated page (Back button after logout).
    header('Cache-Control: no-store');

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId === 0) {
        redirect('login.php');
    }

    $role = (int) ($_SESSION['role_id'] ?? 0);
    if ($role !== APPLICANT_ROLE_ID) {
        redirect(match ($role) {
            1       => 'hr-admin/HR_Admin_Dashboard.php',
            2       => 'faculty-dashboard.php',
            default => 'login.php',
        });
    }

    // Make sure the account still exists and is active.
    $stmt = db()->prepare('SELECT id, email, is_active FROM users WHERE id = :id AND role_id = :role LIMIT 1');
    $stmt->execute(['id' => $userId, 'role' => APPLICANT_ROLE_ID]);
    $row = $stmt->fetch();
    if (!$row || (int) $row['is_active'] !== 1) {
        redirect('authentication/logout.php');
    }

    return [
        'id'    => (int) $row['id'],
        'email' => (string) $row['email'],
        'name'  => (string) ($_SESSION['name'] ?? ''),
    ];
}

/** user_profiles row for this user, or null if none yet. */
function get_profile(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM user_profiles WHERE user_id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function profile_is_complete(?array $profile): bool
{
    return $profile !== null && !empty($profile['profile_completed_at']);
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = (string) ($_POST['_csrf'] ?? '');
    return $sent !== '' && hash_equals($_SESSION['csrf'] ?? '', $sent);
}

/* ---------- small helpers ---------- */

/** First letters of each word, e.g. "Information Technology" -> "IT". */
function initials(string $text): string
{
    $out = '';
    foreach (preg_split('/\s+/', trim($text)) ?: [] as $word) {
        $out .= mb_substr($word, 0, 1);
    }
    return mb_strtoupper($out);
}

/** "APP-0007" style reference shown to the applicant. */
function application_reference(int $id): string
{
    return 'APP-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}

/* ---------- vacancies ---------- */

/** Vacancies applicants may still apply to (status open and not past the closing date). */
function list_open_vacancies(PDO $pdo): array
{
    return $pdo->query(
        "SELECT v.id, v.title, v.employment_type, v.description, v.closing_date, d.department_name
           FROM vacancies v
           JOIN departments d ON d.id = v.department_id
          WHERE v.status = 'open' AND v.closing_date >= CURDATE()
          ORDER BY v.closing_date ASC, v.id ASC"
    )->fetchAll();
}

function find_open_vacancy(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        "SELECT v.id, v.title, v.employment_type, v.description, v.closing_date, d.department_name
           FROM vacancies v
           JOIN departments d ON d.id = v.department_id
          WHERE v.id = :id AND v.status = 'open' AND v.closing_date >= CURDATE()
          LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** vacancy ids this applicant has already applied to. */
function applied_vacancy_ids(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT vacancy_id FROM applications WHERE applicant_user_id = :u AND vacancy_id IS NOT NULL');
    $stmt->execute(['u' => $userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}
