<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/db.php';

$pdo = db();
$uid = (int)($_SESSION['user_id'] ?? 0);

/* HR admins only */
$chk = $pdo->prepare(
    "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
      WHERE u.id = ? AND u.is_active = 1 AND r.role_name = 'hr_admin'"
);
$chk->execute([$uid]);
if ($uid <= 0 || !$chk->fetch()) {
    http_response_code(403);
    exit('Access denied.');
}

/* Find the stored path */
if (isset($_GET['resume'])) {
    $st = $pdo->prepare('SELECT resume_file_path AS file_path, NULL AS original_name, NULL AS mime_type FROM applications WHERE id = ?');
    $st->execute([(int)$_GET['resume']]);
} else {
    $st = $pdo->prepare('SELECT file_path, original_name, mime_type FROM application_documents WHERE id = ?');
    $st->execute([(int)($_GET['id'] ?? 0)]);
}
$row = $st->fetch();
if (!$row || empty($row['file_path'])) {
    http_response_code(404);
    exit('Document not found.');
}

$rel = ltrim(str_replace('\\', '/', (string)$row['file_path']), '/');
if (strpos($rel, '..') !== false) {
    http_response_code(400);
    exit('Invalid path.');
}
$tail = preg_replace('#^(storage|uploads)/#', '', $rel);
$root = dirname(__DIR__); // project root (the folder that contains /config)

/* Try the usual places: project root, /uploads, /storage, /public */
$candidates = [
    "$root/$rel",
    "$root/uploads/$tail",
    "$root/uploads/$rel",
    "$root/storage/$tail",
    "$root/public/$rel",
    "$root/public/uploads/$tail",
    __DIR__ . "/$rel",
];
$file = null;
foreach ($candidates as $c) {
    if (is_file($c)) {
        $file = $c;
        break;
    }
}
if (!$file) {
    http_response_code(404);
    exit('File not found on the server.');
}

$name = $row['original_name'] ?: basename($file);
$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$types = [
    'pdf'  => 'application/pdf',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'txt'  => 'text/plain; charset=utf-8',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$mime   = $types[$ext] ?? ($row['mime_type'] ?: 'application/octet-stream');
$inline = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt'], true); // browsers can show these

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($name));
readfile($file);
exit;