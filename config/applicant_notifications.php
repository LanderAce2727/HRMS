<?php
declare(strict_types=1);

/**
 * Web notifications for applicants (the bell on the applicant pages).
 * Used by the HR page (to create them) and by the applicant pages / api/notifications.php (to read them).
 */

const NOTIF_TZ = 'Asia/Manila';

function notif_tz(): DateTimeZone
{
    return new DateTimeZone(NOTIF_TZ);
}

/** Create a notification. Never throws: a notification problem must not break the HR action. */
function notif_create(PDO $pdo, int $userId, ?int $applicationId, string $category, string $title, string $body): bool
{
    if ($userId <= 0) {
        return false;
    }
    try {
        $pdo->prepare(
            'INSERT INTO applicant_notifications (user_id, application_id, category, title, body, is_read, created_at)
             VALUES (?,?,?,?,?,0,?)'
        )->execute([
            $userId,
            $applicationId,
            $category,
            $title,
            $body,
            (new DateTimeImmutable('now', notif_tz()))->format('Y-m-d H:i:s'),
        ]);
        return true;
    } catch (Throwable $ex) {
        error_log('Notification insert failed: ' . $ex->getMessage());
        return false;
    }
}

/** Newest first. created_at is returned as an ISO-8601 string with the Manila offset. */
function notif_list(PDO $pdo, int $userId, int $limit = 30): array
{
    try {
        $limit = max(1, min(100, $limit));
        $s = $pdo->prepare(
            'SELECT id, category, title, body, is_read, created_at
               FROM applicant_notifications
              WHERE user_id = ?
              ORDER BY created_at DESC, id DESC
              LIMIT ' . $limit
        );
        $s->execute([$userId]);
        $out = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[] = [
                'id'         => (int)$r['id'],
                'category'   => (string)$r['category'],
                'title'      => (string)$r['title'],
                'body'       => (string)$r['body'],
                'is_read'    => (bool)(int)$r['is_read'],
                'created_at' => (new DateTimeImmutable((string)$r['created_at'], notif_tz()))->format(DATE_ATOM),
            ];
        }
        return $out;
    } catch (Throwable $ex) {
        error_log('Notification list failed: ' . $ex->getMessage());
        return [];
    }
}

function notif_unread_count(PDO $pdo, int $userId): int
{
    try {
        $s = $pdo->prepare('SELECT COUNT(*) FROM applicant_notifications WHERE user_id = ? AND is_read = 0');
        $s->execute([$userId]);
        return (int)$s->fetchColumn();
    } catch (Throwable $ex) {
        return 0;
    }
}

/** Mark one notification (or all when $id is null) as read. Only the owner's rows are touched. */
function notif_mark_read(PDO $pdo, int $userId, ?int $id = null): void
{
    try {
        if ($id === null) {
            $pdo->prepare('UPDATE applicant_notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
        } else {
            $pdo->prepare('UPDATE applicant_notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
        }
    } catch (Throwable $ex) {
        error_log('Notification mark-read failed: ' . $ex->getMessage());
    }
}
