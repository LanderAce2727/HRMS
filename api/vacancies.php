<?php
declare(strict_types=1);

/**
 * Open vacancies as ready-made cards (JSON). The job-vacancies page polls this so a vacancy
 * posted by HR appears without a manual refresh, and a closed one disappears.
 */

require_once __DIR__ . '/../config/applicant_auth.php';
require_once __DIR__ . '/../config/vacancy_cards.php';

$user = require_applicant();      // also sends Cache-Control: no-store
$pdo  = db();

header('Content-Type: application/json; charset=utf-8');

$statuses = vacancy_status_map($pdo, (int) $user['id']);
$items    = [];
foreach (list_open_vacancies($pdo) as $i => $job) {
    $jid     = (int) $job['id'];
    $status  = $statuses[$jid] ?? null;
    $items[] = [
        'id'     => $jid,
        'index'  => vacancy_index_label($i),
        'sig'    => vacancy_signature($job),
        'state'  => vacancy_state($status),
        'action' => vacancy_action_html($jid, $status),
        'html'   => vacancy_card_html($job, $i, $status),
    ];
}

echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
