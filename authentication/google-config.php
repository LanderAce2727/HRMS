<?php
// Safe to commit: contains NO secrets. Put the real values in authentication/google-config.local.php
// (git-ignored) or in environment variables. The repo is public.
$local = is_file(__DIR__ . '/google-config.local.php') ? require __DIR__ . '/google-config.local.php' : [];

return $local + [
    'client_id'       => getenv('GOOGLE_CLIENT_ID')     ?: 'YOUR_CLIENT_ID.apps.googleusercontent.com',
    'client_secret'   => getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET',
    // Must match EXACTLY what you add under "Authorized redirect URIs" in Google Cloud Console.
    'redirect_uri'    => 'https://yourdomain.com/authentication/google-callback.php',
    // Restrict to your institution, e.g. 'dnsc.edu.ph'. Leave '' to allow any Google account.
    'allowed_domain'  => '',
    // role_id given to NEW accounts created via Google. TODO: set to your applicant role_id (see `roles` table).
    'applicant_role_id' => 3,
    // true: existing HR (1) / faculty (2) accounts with a matching verified email may also sign in with Google.
    'allow_staff_google' => true,
    // Login page that receives ?error=...
    'login_page'      => '../login.php',
    // Where each role_id lands after Google sign-in.
    'role_redirects'  => [
        1 => '../hr-admin/HR_Admin_Dashboard.php',
        2 => '../faculty-dashboard.php',
        3 => '../applicant-verification.html', // keep in sync with applicant_role_id
    ],
    'fallback_redirect' => '../login.php',
];
