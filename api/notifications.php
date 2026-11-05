<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/applicant_auth.php';
require_once __DIR__ . '/../config/applicant_notifications.php';

$user = require_applicant();
$pdo  = db();
$uid  = (int)$user['id'];

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSON-only: a cross-site form cannot send this content type, so this doubles as a CSRF guard.
    if (stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== 0) {
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported content type.']);
        exit;
    }
    $in     = json_decode((string)file_get_contents('php://input'), true);
    $action = is_array($in) ? (string)($in['action'] ?? '') : '';
    if ($action === 'read' && isset($in['id'])) {
        notif_mark_read($pdo, $uid, (int)$in['id']);
    } elseif ($action === 'read_all') {
        notif_mark_read($pdo, $uid, null);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action.']);
        exit;
    }
}

echo json_encode(
    ['unread' => notif_unread_count($pdo, $uid), 'items' => notif_list($pdo, $uid, 30)],
    JSON_UNESCAPED_UNICODE
);
