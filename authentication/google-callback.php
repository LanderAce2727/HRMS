<?php
// Step 2: Google redirects back here. Verify, then log in or register.
session_start();
$cfg = require __DIR__ . '/google-config.php';

function fail(string $code) {
    $cfg = require __DIR__ . '/google-config.php';
    header('Location: ' . $cfg['login_page'] . '?error=' . urlencode($code));
    exit;
}

// ---- 1. Basic checks -------------------------------------------------------
if (isset($_GET['error']))                      fail('google_denied');
if (empty($_GET['code']) || empty($_GET['state'])) fail('google_failed');
if (empty($_SESSION['g_state']) || !hash_equals($_SESSION['g_state'], $_GET['state'])) fail('google_failed');

$verifier = $_SESSION['g_verifier'] ?? '';
$nonce    = $_SESSION['g_nonce'] ?? '';
unset($_SESSION['g_state'], $_SESSION['g_verifier'], $_SESSION['g_nonce']);

// ---- 2. Exchange the code for tokens (server to server) --------------------
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $cfg['client_id'],
        'client_secret' => $cfg['client_secret'],
        'redirect_uri'  => $cfg['redirect_uri'],
        'grant_type'    => 'authorization_code',
        'code_verifier' => $verifier,
    ]),
]);
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokens = json_decode((string)$response, true);
if ($status !== 200 || empty($tokens['id_token'])) fail('google_failed');

// ---- 3. Validate the ID token ---------------------------------------------
// The token came directly from Google over TLS, so signature checking can be skipped
// (per Google's docs). The claims still must be validated.
$parts = explode('.', $tokens['id_token']);
if (count($parts) !== 3) fail('google_failed');
$claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

$validIssuer = in_array($claims['iss'] ?? '', ['https://accounts.google.com', 'accounts.google.com'], true);
if (!$validIssuer
    || ($claims['aud'] ?? '') !== $cfg['client_id']
    || ($claims['exp'] ?? 0) < time()
    || !hash_equals($nonce, $claims['nonce'] ?? '')
    || empty($claims['sub'])
    || empty($claims['email'])
    || empty($claims['email_verified'])) {
    fail('google_failed');
}

$sub   = $claims['sub'];                       // Google's stable unique ID
$email = strtolower($claims['email']);
$name  = $claims['name'] ?? $email;

// Optional institution-only restriction
if ($cfg['allowed_domain']) {
    $domain = substr(strrchr($email, '@'), 1);
    if (strcasecmp($domain, $cfg['allowed_domain']) !== 0) fail('google_domain');
}

// ---- 4. Find, link, or create the user ------------------------------------
require_once __DIR__ . '/../config/db.php';

try {
    $pdo = db();

    // 4a. Already linked to this Google account?
    $stmt = $pdo->prepare('SELECT id, username, email, role_id, branch_id, is_active FROM users WHERE google_sub = :sub LIMIT 1');
    $stmt->execute(['sub' => $sub]);
    $user = $stmt->fetch();

    // 4b. Existing account with the same (Google-verified) email? Link it.
    if (!$user) {
        $stmt = $pdo->prepare('SELECT id, username, email, role_id, branch_id, is_active FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            $isStaff = in_array((int) $user['role_id'], [1, 2], true);
            if ($isStaff && !$cfg['allow_staff_google']) fail('google_failed');
            $pdo->prepare('UPDATE users SET google_sub = :sub WHERE id = :id')
                ->execute(['sub' => $sub, 'id' => $user['id']]);
        }
    }

    // 4c. Otherwise register a NEW APPLICANT account.
    if (!$user) {
        $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role_id, branch_id, is_active, google_sub)
             VALUES (:username, :email, :hash, :role_id, NULL, 1, :sub)'
        )->execute([
            'username' => $email,
            'email'    => $email,
            'hash'     => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // unusable random password
            'role_id'  => $cfg['applicant_role_id'],
            'sub'      => $sub,
        ]);
        $stmt = $pdo->prepare('SELECT id, username, email, role_id, branch_id, is_active FROM users WHERE id = :id');
        $stmt->execute(['id' => $pdo->lastInsertId()]);
        $user = $stmt->fetch();
    }

    if (!$user || (int) $user['is_active'] !== 1) fail('google_failed');

} catch (Throwable $e) {
    error_log($e->getMessage());
    fail('google_failed');
}

// ---- 5. Start the session (same keys as authentication/login.php) ---------
session_regenerate_id(true);

$_SESSION['user_id']   = (int) $user['id'];
$_SESSION['username']  = $user['username'];
$_SESSION['email']     = $user['email'];
$_SESSION['role_id']   = (int) $user['role_id'];
$_SESSION['branch_id'] = $user['branch_id'];
// Extras used by the applicant pages
$_SESSION['name']      = $name;
$_SESSION['picture']   = $claims['picture'] ?? '';

$dest = $cfg['role_redirects'][(int) $user['role_id']] ?? $cfg['fallback_redirect'];
header('Location: ' . $dest);
exit;
