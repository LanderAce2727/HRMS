<?php
/**
 * TEMPORARY diagnostic for the Gmail setup. Put it in the project root (next to composer.json),
 * open it in the browser (e.g. http://localhost:8085/test_mail.php), then DELETE it.
 * It sends one test email to the account's own address and never prints the password.
 */
declare(strict_types=1);

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local use only.');
}
header('Content-Type: text/html; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$root = __DIR__;
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function line(bool $ok, string $label, string $fix = ''): void {
    echo '<p style="margin:6px 0">' . ($ok ? '✅' : '❌') . ' <b>' . h($label) . '</b>'
       . (!$ok && $fix !== '' ? '<br><span style="margin-left:26px;color:#a12b2b">→ ' . h($fix) . '</span>' : '') . '</p>';
}
function stop(): never { echo '<p><b>Fix the ❌ above, reload this page, and it will continue.</b></p>'; exit; }

echo '<body style="font:15px/1.5 system-ui,sans-serif;max-width:820px;margin:30px auto;padding:0 16px">';
echo '<h2>HRMS email diagnostic</h2><p>Project folder: <code>' . h($root) . '</code> &nbsp; PHP ' . h(PHP_VERSION) . '</p>';

/* 1. PHP extensions */
$missing = array_values(array_filter(['openssl', 'mbstring', 'ctype', 'filter', 'hash'], fn ($e) => !extension_loaded($e)));
line(!$missing, 'PHP extensions (openssl, mbstring, ctype, filter, hash)',
    $missing ? 'Missing: ' . implode(', ', $missing) . '. In C:\\xampp\\php\\php.ini remove the ";" before extension=' . $missing[0] . ', then restart Apache.' : '');
if ($missing) stop();

/* 2. vendor/ */
$autoload = $root . '/vendor/autoload.php';
line(is_file($autoload), 'vendor/autoload.php exists', 'Open a terminal in ' . $root . ' and run:  composer install');
if (!is_file($autoload)) stop();
require $autoload;
line(class_exists(PHPMailer::class) && class_exists(Dotenv\Dotenv::class), 'PHPMailer and Dotenv are installed',
    'Run:  composer require phpmailer/phpmailer vlucas/phpdotenv');
if (!class_exists(PHPMailer::class) || !class_exists(Dotenv\Dotenv::class)) stop();

/* 3. .env */
$hasEnv = is_file($root . '/.env');
$fix = 'Create a file named exactly ".env" in ' . $root . ' containing two lines:  EMAIL_USER=your-hrms@gmail.com  and  EMAIL_PASS=your16characterapppassword   (the file is not in Git, so it must be created on every computer).';
if (!$hasEnv && is_file($root . '/.env.txt')) $fix .= ' You have ".env.txt" - rename it to ".env" (turn on File name extensions in File Explorer).';
line($hasEnv, '.env file found', $fix);
if (!$hasEnv) stop();

try {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
    line(true, '.env can be read');
} catch (Throwable $e) {
    line(false, '.env could not be read', $e->getMessage() . ' - no spaces or quotes in the values, one KEY=value per line.');
    stop();
}

/* 4. credentials (the password is never shown) */
$user = (string) ($_ENV['EMAIL_USER'] ?? '');
$pass = (string) ($_ENV['EMAIL_PASS'] ?? '');
line($user !== '' && filter_var($user, FILTER_VALIDATE_EMAIL) !== false, 'EMAIL_USER is set: ' . ($user !== '' ? $user : '(empty)'),
    'Add EMAIL_USER=your-hrms@gmail.com to .env, then restart the PHP server.');
line(strlen($pass) === 16 && !preg_match('/\s/', $pass), 'EMAIL_PASS has 16 characters, no spaces (length found: ' . strlen($pass) . ')',
    'Use the 16-letter App Password from myaccount.google.com/apppasswords, not the Gmail password. Write it without spaces or quotes.');
if ($user === '' || $pass === '') stop();

/* 5. can this computer reach Gmail? */
$errno = 0; $errstr = '';
$sock = @fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 10);
line($sock !== false, 'Connection to smtp.gmail.com:465 works',
    'Error ' . $errno . ': ' . $errstr . '. Common causes: no internet, a school/office network that blocks port 465 (try a phone hotspot), antivirus/firewall, or missing SSL certificates (php.ini: openssl.cafile pointing to cacert.pem from curl.se/ca/cacert.pem).');
if ($sock) fclose($sock); else stop();

/* 6. real send, with the conversation shown (credentials hidden) */
echo '<h3>Sending a test email to ' . h($user) . '</h3><pre style="background:#f4f4f4;padding:12px;overflow:auto;font-size:13px">';
$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug   = 2;
    $mail->Debugoutput = function (string $str): void {
        $str = preg_replace('/(CLIENT -> SERVER: )[A-Za-z0-9+\/=]{12,}/', '$1[hidden]', $str);
        echo h(rtrim($str)) . "\n";
    };
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $user;
    $mail->Password   = $pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 15;
    $mail->setFrom($user, 'HRMS');
    $mail->addAddress($user);
    $mail->Subject = 'HRMS email test';
    $mail->Body    = 'If you can read this, email sending works on this computer.';
    $mail->send();
    echo '</pre>';
    line(true, 'SENT. Check the inbox of ' . $user . ' (and Spam). Email works - now delete test_mail.php.');
} catch (Exception $e) {
    echo '</pre>';
    $why = $mail->ErrorInfo;
    $hint = 'See the conversation above.';
    if (stripos($why, 'authenticate') !== false) $hint = 'Gmail refused the login: the App Password is wrong, was deleted, belongs to a different Google account than EMAIL_USER, or 2-Step Verification is off. Create a new App Password and update .env.';
    line(false, 'Send failed: ' . $why, $hint);
}
