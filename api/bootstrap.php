<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Manila');
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $sessionDirectory=dirname(__DIR__).'/storage/sessions';
    if(!is_dir($sessionDirectory))mkdir($sessionDirectory,0700,true);
    session_save_path($sessionDirectory);
    session_name('DNSC_HRMS_SESSION');
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax','path'=>'/']);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
function db(): PDO {
    static $connection;
    if (!$connection) $connection = new PDO('mysql:host='.(getenv('HRMS_DB_HOST') ?: '127.0.0.1').';port='.(getenv('HRMS_DB_PORT') ?: '3306').';dbname='.(getenv('HRMS_DB_NAME') ?: 'smart_hrms').';charset=utf8mb4',getenv('HRMS_DB_USER') ?: 'root',getenv('HRMS_DB_PASSWORD') ?: '',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $connection->exec("SET time_zone = '+08:00'");
    return $connection;
}
function rows(string $sql,array $params=[]): array { $q=db()->prepare($sql);$q->execute($params);return $q->fetchAll(); }
function one(string $sql,array $params=[]): ?array { return rows($sql,$params)[0] ?? null; }
function execute(string $sql,array $params=[]): void { $q=db()->prepare($sql);$q->execute($params); }
function result(array $data,int $code=200): never { http_response_code($code);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit; }
function fail(string $message,int $code=422): never { result(['success'=>false,'message'=>$message],$code); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function check_csrf(): void { if(!hash_equals(csrf(),(string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? ''))) fail('Your form expired. Reload the page and try again.',419); }
function user(): ?array {
    static $checked=false,$current=null;
    if($checked)return $current;
    $checked=true;
    if(empty($_SESSION['user_id']))return null;
    if(time()-(int)($_SESSION['last_activity'] ?? 0)>1800){$_SESSION=[];return null;}
    $current=one('SELECT u.id,u.username,u.email,u.branch_id,u.session_version,r.role_name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.is_active=1',[(int)$_SESSION['user_id']]);
    if(!$current||(int)$current['session_version']!==(int)($_SESSION['session_version'] ?? 0)){$_SESSION=[];$current=null;return null;}
    unset($current['session_version']);
    $_SESSION['last_activity']=time();return $current;
}
function require_user(string ...$roles): array { $u=user();if(!$u)fail('Please log in to continue.',401);if($roles&&!in_array($u['role'],$roles,true))fail('You do not have permission for this action.',403);return $u; }
function employee(array $u): array { $e=one('SELECT e.*,d.branch_id FROM employees e JOIN departments d ON d.id=e.department_id WHERE e.user_id=?',[$u['id']]);if(!$e)fail('Your employee record has not been created. Contact HR.',409);return $e; }
function audit(string $action,string $table,int $id,string $details=''): void { $u=user();if($u)execute('INSERT INTO audit_logs(user_id,action_type,target_table,record_id,details) VALUES(?,?,?,?,?)',[$u['id'],$action,$table,$id,$details]); }
function text(array $in,string $key,int $max=10000,bool $required=true): string { $s=trim((string)($in[$key] ?? ''));if(($required&&$s==='')||mb_strlen($s)>$max)fail('Enter a valid '.str_replace('_',' ',$key).'.');return $s; }
function choice(array $in,string $key,array $allowed): string { $s=text($in,$key);if(!in_array($s,$allowed,true))fail('Invalid '.str_replace('_',' ',$key).'.');return $s; }
function date_value(array $in,string $key): string { $s=text($in,$key,10);$d=DateTimeImmutable::createFromFormat('!Y-m-d',$s);if(!$d||$d->format('Y-m-d')!==$s)fail('Enter a valid '.str_replace('_',' ',$key).'.');return $s; }
function positive_id(array $in,string $key='id'): int { $value=filter_var($in[$key] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$value)fail('Select a valid '.str_replace('_',' ',$key).'.');return $value; }
function setting(string $key,string $fallback=''): string { return one('SELECT setting_value FROM system_settings WHERE setting_key=?',[$key])['setting_value'] ?? $fallback; }
function route_for(string $role): string { return match($role){'hr_admin'=>'hr-admin/HR_Admin_Dashboard.html','employee'=>'employee/Employee_Employee_Profile.html','student'=>'student/Student_Verification_Status.html',default=>'applicant/Applicant_My_Applications.html'}; }
function login_user(int $id): void { session_regenerate_id(true);$_SESSION['user_id']=$id;$_SESSION['session_version']=(int)one('SELECT session_version FROM users WHERE id=?',[$id])['session_version'];$_SESSION['last_activity']=time();$_SESSION['csrf']=bin2hex(random_bytes(32)); }
function transaction(callable $fn): mixed { db()->beginTransaction();try{$value=$fn();db()->commit();return $value;}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();throw $e;} }
set_exception_handler(function(Throwable $e){error_log('HRMS: '.$e->getMessage());if($e instanceof PDOException && $e->getCode()==='23000')fail('This record conflicts with an existing record. Check the email, employee number, or dates.',409);fail('The service could not complete this request. Please contact your administrator.',503);});
