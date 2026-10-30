<?php
// Step 1: send the user to Google.
session_start();
$cfg = require __DIR__ . '/google-config.php';

$b64url = fn($bin) => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');

$state    = bin2hex(random_bytes(16));   // CSRF protection
$nonce    = bin2hex(random_bytes(16));   // replay protection for the ID token
$verifier = $b64url(random_bytes(32));   // PKCE
$challenge = $b64url(hash('sha256', $verifier, true));

$_SESSION['g_state']    = $state;
$_SESSION['g_nonce']    = $nonce;
$_SESSION['g_verifier'] = $verifier;

$params = [
    'client_id'             => $cfg['client_id'],
    'redirect_uri'          => $cfg['redirect_uri'],
    'response_type'         => 'code',
    'scope'                 => 'openid email profile',
    'state'                 => $state,
    'nonce'                 => $nonce,
    'code_challenge'        => $challenge,
    'code_challenge_method' => 'S256',
    'prompt'                => 'select_account',
];
if ($cfg['allowed_domain']) {
    $params['hd'] = $cfg['allowed_domain']; // UI hint only; enforced again in the callback
}

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
