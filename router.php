<?php
declare(strict_types=1);
// Router for php -S and Apache (.htaccess). Private files are never served.
$relative=(string)($_GET['route'] ?? rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/',PHP_URL_PATH) ?: '/'));
$relative=ltrim(str_replace('\\','/',$relative),'/');if($relative==='')$relative='index.html';
if(str_contains($relative,'..')||str_contains($relative,"\0")){http_response_code(404);exit;}
$allowedApi=['api/system.php','api/login.php','api/logout.php','api/apply.php','api/session.php'];
$root=__DIR__;
if(in_array($relative,$allowedApi,true)){require $root.'/'.$relative;exit;}
if(str_starts_with($relative,'assets/')&&preg_match('/\.(css|js|svg|png|jpg|jpeg|webp|ico|woff2)$/i',$relative)&&is_file($root.'/'.$relative)){
    $mime=['css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','ico'=>'image/x-icon','woff2'=>'font/woff2'];header('Content-Type: '.$mime[strtolower(pathinfo($relative,PATHINFO_EXTENSION))]);header('X-Content-Type-Options: nosniff');readfile($root.'/'.$relative);exit;
}
if(!preg_match('~^(?:index|Attendance|Campus Sign In|DNSC_Sign_In_Single_Card|DNSC_HR_Dashboard)\.html$|^(?:authentication|applicant|hr-admin|employee|student)/[A-Za-z_]+\.html$~',$relative)||!is_file($root.'/'.$relative)){http_response_code(404);exit('Page not found.');}
require_once $root.'/api/bootstrap.php';
header('Cache-Control: no-store');header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
$base=isset($_GET['route'])?rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/'):'';
if(in_array(basename($relative),['Campus Sign In.html','DNSC_Sign_In_Single_Card.html','Authentication_Login.html','Authentication_Login_Single_Card.html','Authentication_Applicant_Registration.html','Authentication_Student_Registration.html','Student_Student_Registration.html'],true)&&($signedIn=user())){header('Location: '.$base.'/'.route_for($signedIn['role']));exit;}
$role=null;
if(str_starts_with($relative,'hr-admin/')||$relative==='DNSC_HR_Dashboard.html')$role='hr_admin';
if(str_starts_with($relative,'employee/')||$relative==='Attendance.html')$role='employee';
if(in_array(basename($relative),['Applicant_My_Applications.html','Applicant_Application_Status.html'],true))$role='applicant';
if(str_starts_with($relative,'student/')&&basename($relative)!=='Student_Student_Registration.html')$role='student';
if($role){$u=user();if(!$u){header('Location: '.$base.'/authentication/Authentication_Login.html');exit;}if($u['role']!==$role){http_response_code(403);echo '<!doctype html><meta name="viewport" content="width=device-width,initial-scale=1"><title>Access denied</title><h1>Access denied</h1><p>This page belongs to a different account role.</p><a href="'.htmlspecialchars($base.'/'.route_for($u['role']),ENT_QUOTES).'">Return to your dashboard</a>';exit;}}
readfile($root.'/'.$relative);
