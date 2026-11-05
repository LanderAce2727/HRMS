<?php
declare(strict_types=1);

/**
 * Vacancy cards for the applicant pages.
 * Shared by job-vacancies.php (first paint) and api/vacancies.php (live updates),
 * so a card looks exactly the same whichever way it arrives.
 */

require_once __DIR__ . '/applicant_auth.php';

const APPLICATION_STATUSES = ['submitted', 'reviewing', 'interviewed', 'hired', 'rejected'];

/**
 * vacancy_id => status of this applicant's latest application for it.
 * Statuses: submitted | reviewing | interviewed | hired | rejected
 */
function vacancy_status_map(PDO $pdo, int $userId): array
{
    $map  = [];
    $stmt = $pdo->prepare(
        'SELECT vacancy_id, status FROM applications
          WHERE applicant_user_id = ? AND vacancy_id IS NOT NULL
          ORDER BY created_at ASC, id ASC'
    );
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[(int) $row['vacancy_id']] = (string) $row['status'];   // newest row wins
    }
    foreach (applied_vacancy_ids($pdo, $userId) as $id) {
        $map[$id] = $map[$id] ?? 'submitted';
    }
    return $map;
}

/** The Apply Now link, or the disabled status label when the applicant already applied. */
function vacancy_action_html(int $jid, ?string $status): string
{
    if ($status !== null) {
        $cls = in_array($status, APPLICATION_STATUSES, true) ? $status : 'submitted';
        return '<span class="demo-button application-status application-status-' . e($cls) . '"'
            . ' data-vacancy-action data-state="status:' . e($cls) . '" role="status" aria-disabled="true"'
            . ' title="Your application status for this job">' . e(ucfirst($status)) . '</span>';
    }
    return '<a class="demo-button primary" data-vacancy-action data-state="apply"'
        . ' href="job-application.php?job=' . $jid . '">Apply Now</a>';
}

function vacancy_state(?string $status): string
{
    if ($status === null) {
        return 'apply';
    }
    return 'status:' . (in_array($status, APPLICATION_STATUSES, true) ? $status : 'submitted');
}

/** Changes when HR edits the vacancy's visible details, so open pages know to redraw the card. */
function vacancy_signature(array $job): string
{
    return md5(implode('|', [
        $job['title'], $job['department_name'], $job['employment_type'], $job['description'], $job['closing_date'],
    ]));
}

function vacancy_index_label(int $i): string
{
    return '#' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
}

function vacancy_card_html(array $job, int $i, ?string $status): string
{
    $jid      = (int) $job['id'];
    $dept     = (string) $job['department_name'];
    $deadline = date('F j, Y', strtotime((string) $job['closing_date']));
    $search   = $job['title'] . ' ' . $dept . ' ' . $job['employment_type'] . ' ' . $job['description'];

    ob_start();
?><article class="demo-panel vacancy-card" data-job="<?= $jid ?>" data-sig="<?= e(vacancy_signature($job)) ?>" data-search="<?= e($search) ?>"><div class="vacancy-card-top"><span class="vacancy-mark" aria-hidden="true">D</span><span class="vacancy-publisher"><small>Posted by</small><strong>DNSC</strong></span><span class="vacancy-index"><?= e(vacancy_index_label($i)) ?></span></div><div class="vacancy-media" role="img" aria-label="Image placeholder for <?= e($dept) ?>"><span class="vacancy-media-initials" aria-hidden="true"><?= e(initials($dept)) ?></span><span class="vacancy-media-caption" aria-hidden="true"><?= e($dept) ?></span></div><div class="vacancy-summary"><span class="vacancy-department"><?= e($dept) ?></span><h2 class="vacancy-title"><?= e($job['title']) ?></h2><span class="vacancy-excerpt"><?= e($job['description']) ?></span></div><div class="vacancy-card-bottom"><span class="vacancy-type"><?= e($job['employment_type']) ?></span><span class="vacancy-deadline">Apply by <strong><?= e($deadline) ?></strong></span></div><div class="vacancy-actions"><button type="button" class="vacancy-toggle" id="vacancy-toggle-<?= $jid ?>" aria-expanded="false" aria-controls="vacancy-details-<?= $jid ?>"><span class="vacancy-toggle-label">View details</span><svg class="vacancy-chevron" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button><?= vacancy_action_html($jid, $status) ?></div><div class="vacancy-details" id="vacancy-details-<?= $jid ?>" role="region" aria-labelledby="vacancy-toggle-<?= $jid ?>" aria-hidden="true" inert><div class="vacancy-details-clip"><div class="vacancy-details-content"><h3>Job Details</h3><dl><dt>Job Description</dt><dd><?= e($job['description']) ?></dd><dt>Employment Type</dt><dd><?= e($job['employment_type']) ?></dd></dl><button type="button" class="demo-button vacancy-close">Close details</button></div></div></div></article><?php
    return (string) ob_get_clean();
}
