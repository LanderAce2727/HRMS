<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';
$action=(string)($_GET['action'] ?? 'session');
$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
$in=$_POST;
if(str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')){$in=json_decode(file_get_contents('php://input'),true);if(!is_array($in))fail('Invalid request body.',400);}
if($method==='POST')check_csrf();
elseif($method!=='GET')fail('Method not allowed.',405);
$writeActions=['login','register','logout','save_vacancy','apply','review_application','save_employee','update_profile','employee_status','scan','correct_attendance','generate_payslip','request_leave','review_leave','review_student','evaluate','schedule_interview','change_password','save_settings'];
if(in_array($action,$writeActions,true)&&$method!=='POST')fail('Use POST for this action.',405);
switch($action){
case 'session':
    $u=user();result(['success'=>true,'user'=>$u,'csrf'=>csrf(),'dashboard'=>$u?route_for($u['role']):null]);
case 'login':
    $email=strtolower(text($in,'email',100));$password=text($in,'password',1024);
    $key=hash('sha256',($_SERVER['REMOTE_ADDR'] ?? '').'|'.$email);
    if((int)one('SELECT COUNT(*) n FROM login_attempts WHERE attempt_key=? AND created_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)',[$key])['n']>=8)fail('Too many login attempts. Please try again in 15 minutes.',429);
    $u=one('SELECT u.*,r.role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=?',[$email]);
    if(!$u||!(int)$u['is_active']||!password_verify($password,$u['password_hash'])){execute('INSERT INTO login_attempts(attempt_key) VALUES(?)',[$key]);fail('Incorrect email or password.',401);}
    execute('DELETE FROM login_attempts WHERE attempt_key=?',[$key]);login_user((int)$u['id']);
    result(['success'=>true,'redirect'=>route_for($u['role_name'])]);
case 'logout':
    $_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'', ['expires'=>time()-42000,'path'=>$p['path'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);}session_destroy();result(['success'=>true]);
case 'register':
    $email=strtolower(text($in,'email',100));if(!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Enter a valid email address.');
    $password=text($in,'password',1024);if(strlen($password)<12||strlen($password)>72)fail('Use a password between 12 and 72 bytes.');
    $name=text($in,'full_name',150);$phone=text($in,'contact_number',20,false);$role=choice($in,'role',['applicant','student']);
    if($role==='student'){$number=text($in,'student_number',50);$program=text($in,'program',150);$year=positive_id($in,'year_level');if($year>6)fail('Select a valid year level.');}
    transaction(function()use($email,$password,$name,$phone,$role,$in){
        $roleRow=one('SELECT id FROM roles WHERE role_name=?',[$role]);if(!$roleRow)fail('This account type has not been configured.',503);
        if(one('SELECT id FROM users WHERE email=?',[$email]))fail('An account with this email already exists.',409);
        execute('INSERT INTO users(role_id,username,email,password_hash) VALUES(?,?,?,?)',[$roleRow['id'],'u_'.bin2hex(random_bytes(12)),$email,password_hash($password,PASSWORD_DEFAULT)]);$id=(int)db()->lastInsertId();
        execute('INSERT INTO user_profiles(user_id,full_name,contact_number) VALUES(?,?,?)',[$id,$name,$phone]);
        if($role==='student')execute('INSERT INTO students(user_id,student_number,full_name,program,year_level) VALUES(?,?,?,?,?)',[$id,$in['student_number'],$name,$in['program'],(int)$in['year_level']]);
        login_user($id);
    });result(['success'=>true,'redirect'=>route_for($role)],201);
case 'change_password':
    $u=require_user();$old=text($in,'current_password',1024);$new=text($in,'new_password',1024);
    $record=one('SELECT password_hash FROM users WHERE id=?',[$u['id']]);
    if(!password_verify($old,$record['password_hash']))fail('Your current password is incorrect.',403);
    if(strlen($new)<12||strlen($new)>72)fail('Use a new password between 12 and 72 bytes.');
    execute('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE id=?',[password_hash($new,PASSWORD_DEFAULT),$u['id']]);$_SESSION['session_version']=(int)one('SELECT session_version FROM users WHERE id=?',[$u['id']])['session_version'];session_regenerate_id(true);audit('change_password','users',(int)$u['id']);result(['success'=>true,'message'=>'Your password has been changed.']);
case 'settings':
    require_user('hr_admin');result(['success'=>true,'academic_period'=>setting('academic_period'),'late_after'=>setting('late_after','08:00:00')]);
case 'save_settings':
    require_user('hr_admin');$period=text($in,'academic_period',30,false);$time=text($in,'late_after',5);
    if(!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$time))fail('Enter a valid work start time.');
    transaction(function()use($period,$time){foreach(['academic_period'=>$period,'late_after'=>$time.':00'] as $key=>$value)execute('INSERT INTO system_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',[$key,$value]);audit('save_settings','system_settings',0);});result(['success'=>true,'message'=>'System settings saved.']);
case 'metadata':
    require_user('hr_admin');result(['success'=>true,'departments'=>rows('SELECT id,department_name,branch_id FROM departments ORDER BY department_name'),'branches'=>rows('SELECT id,branch_name FROM branches ORDER BY branch_name')]);
case 'vacancies':
    $u=user();$admin=$u&&$u['role']==='hr_admin';$where=$admin?'1=1':"v.status='open'";$args=[];
    if(isset($_GET['id'])){$where.=' AND v.id=?';$args[]=positive_id($_GET);}
    result(['success'=>true,'records'=>rows("SELECT v.*,d.department_name,b.branch_name FROM vacancies v JOIN departments d ON d.id=v.department_id JOIN branches b ON b.id=v.branch_id WHERE $where ORDER BY v.created_at DESC,v.id DESC",$args)]);
case 'save_vacancy':
    $u=require_user('hr_admin');$department=one('SELECT * FROM departments WHERE id=?',[positive_id($in,'department_id')]);if(!$department)fail('Select an existing department.');
    $values=[text($in,'job_title',150),text($in,'job_description',30000),text($in,'requirements',30000,false),choice($in,'category',['teaching','non-teaching']),choice($in,'status',['draft','open','closed']),$department['id'],$department['branch_id']];
    transaction(function()use($in,$values,$u){if(!empty($in['id'])){$id=positive_id($in);if(!one('SELECT id FROM vacancies WHERE id=? FOR UPDATE',[$id]))fail('Vacancy not found.',404);execute('UPDATE vacancies SET job_title=?,job_description=?,requirements=?,category=?,status=?,department_id=?,branch_id=? WHERE id=?',[...$values,$id]);}else{execute('INSERT INTO vacancies(job_title,job_description,requirements,category,status,department_id,branch_id,posted_by_user_id) VALUES(?,?,?,?,?,?,?,?)',[...$values,$u['id']]);$id=(int)db()->lastInsertId();}audit('save_vacancy','vacancies',$id);});result(['success'=>true,'message'=>'Vacancy saved.']);
case 'applications':
    $u=require_user('hr_admin','applicant');$where=$u['role']==='hr_admin'?'1=1':'a.applicant_user_id=?';$args=$u['role']==='hr_admin'?[]:[$u['id']];
    if(isset($_GET['id'])){$where.=' AND a.id=?';$args[]=positive_id($_GET);}
    $records=rows("SELECT a.id,a.vacancy_id,a.full_name,a.email,a.contact_number,a.position_applied,a.highest_education,a.experience_details,a.cover_letter,a.status,a.created_at,a.reviewed_at,v.job_title FROM applications a LEFT JOIN vacancies v ON v.id=a.vacancy_id WHERE $where ORDER BY a.created_at DESC,a.id DESC",$args);
    foreach($records as &$r)$r['interviews']=rows('SELECT interview_date,interview_time,location_or_link,status FROM interview_schedules WHERE application_id=? ORDER BY interview_date DESC',[$r['id']]);unset($r);
    result(['success'=>true,'records'=>$records]);
case 'apply':
    $u=require_user('applicant');$id=positive_id($in,'vacancy_id');$name=text($in,'full_name',150);$phone=text($in,'contact_number',20);$education=text($in,'highest_education',50);$experience=text($in,'experience_details',20000,false);$letter=text($in,'cover_letter',20000,false);
    $file=$_FILES['resume'] ?? null;if(!$file||$file['error']!==UPLOAD_ERR_OK||$file['size']>5*1024*1024||$file['size']<5)fail('Upload a PDF resume up to 5 MB.');
    if((new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])!=='application/pdf'||file_get_contents($file['tmp_name'],false,null,0,5)!=='%PDF-')fail('Your resume must be a PDF.');
    $folder=dirname(__DIR__).'/storage/resumes';if(!is_dir($folder))mkdir($folder,0700,true);$path='storage/resumes/'.bin2hex(random_bytes(24)).'.pdf';$absolute=dirname(__DIR__).'/'.$path;
    $applicationId=0;
    try{
        db()->beginTransaction();$job=one("SELECT * FROM vacancies WHERE id=? AND status='open' FOR UPDATE",[$id]);if(!$job)fail('This vacancy is no longer open.',409);
        if(one('SELECT id FROM applications WHERE vacancy_id=? AND applicant_user_id=?',[$id,$u['id']]))fail('You have already applied for this position.',409);
        if(!move_uploaded_file($file['tmp_name'],$absolute))throw new RuntimeException('Resume could not be saved.');
        execute("INSERT INTO applications(vacancy_id,applicant_user_id,full_name,email,contact_number,position_applied,highest_education,experience_details,cover_letter,resume_file_path,status) VALUES(?,?,?,?,?,?,?,?,?,?,'submitted')",[$id,$u['id'],$name,$u['email'],$phone,mb_substr($job['job_title'],0,50),$education,$experience,$letter,$path]);$applicationId=(int)db()->lastInsertId();audit('submit_application','applications',$applicationId);db()->commit();
    }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();if(is_file($absolute))unlink($absolute);throw $e;}
    result(['success'=>true,'id'=>$applicationId,'message'=>'Your application has been submitted.'],201);
case 'resume':
    $u=require_user('hr_admin','applicant');$a=one('SELECT applicant_user_id,resume_file_path FROM applications WHERE id=?',[positive_id($_GET)]);if(!$a||($u['role']!=='hr_admin'&&(int)$a['applicant_user_id']!==(int)$u['id']))fail('Resume not found.',404);
    $root=dirname(__DIR__);$file=realpath($root.'/'.$a['resume_file_path']);$valid=false;
    foreach(['/storage/resumes/','/uploads/resumes/'] as $allowed){$dir=realpath($root.$allowed);if($dir&&$file&&str_starts_with($file,$dir.DIRECTORY_SEPARATOR))$valid=true;}
    if(!$valid||!is_file($file))fail('Resume file is unavailable.',404);
    header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="resume.pdf"');header('Cache-Control: no-store');readfile($file);exit;
case 'review_application':
    $u=require_user('hr_admin');$id=positive_id($in);$status=choice($in,'status',['submitted','under_review','accepted','rejected']);
    transaction(function()use($id,$status,$u){if(!one('SELECT id FROM applications WHERE id=? FOR UPDATE',[$id]))fail('Application not found.',404);execute('UPDATE applications SET status=?,reviewed_by_user_id=?,reviewed_at=NOW() WHERE id=?',[$status,$u['id'],$id]);audit('review_application','applications',$id,$status);});result(['success'=>true,'message'=>'Application status updated.']);
case 'schedule_interview':
    $u=require_user('hr_admin');$id=positive_id($in,'application_id');$date=date_value($in,'interview_date');$time=text($in,'interview_time',5);if(!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$time))fail('Enter a valid interview time.');
    if(!one('SELECT id FROM applications WHERE id=?',[$id]))fail('Application not found.',404);
    execute('INSERT INTO interview_schedules(application_id,interviewer_user_id,interview_date,interview_time,location_or_link) VALUES(?,?,?,?,?)',[$id,$u['id'],$date,$time,text($in,'location_or_link',255)]);audit('schedule_interview','interview_schedules',(int)db()->lastInsertId());result(['success'=>true,'message'=>'Interview scheduled. The applicant can view it in their account.']);
case 'employees':
    $u=require_user('hr_admin','employee');$filter=$u['role']==='hr_admin'?'1=1':'e.user_id=?';$args=$u['role']==='hr_admin'?[]:[$u['id']];
    result(['success'=>true,'records'=>rows("SELECT e.*,u.email,d.department_name,p.contact_number,p.address,(SELECT daily_rate FROM pay_rates pr WHERE pr.employee_id=e.id AND pr.effective_date<=CURDATE() ORDER BY pr.effective_date DESC,pr.id DESC LIMIT 1) daily_rate FROM employees e JOIN users u ON u.id=e.user_id JOIN departments d ON d.id=e.department_id LEFT JOIN user_profiles p ON p.user_id=e.user_id WHERE $filter ORDER BY e.last_name,e.first_name",$args)]);
case 'save_employee':
    $u=require_user('hr_admin');$email=strtolower(text($in,'email',100));if(!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Enter a valid work email.');$first=text($in,'first_name',50);$last=text($in,'last_name',50);$number=text($in,'employee_number',30);$rfid=text($in,'rfid_card_number',50,false);$department=positive_id($in,'department_id');$position=text($in,'position_title',100);$hired=date_value($in,'date_hired');$rateDate=isset($in['rate_effective_date'])?date_value($in,'rate_effective_date'):date('Y-m-d');$faculty=choice($in,'is_faculty',['0','1']);$rate=$in['daily_rate'] ?? '';if(!preg_match('/^\d{1,7}(\.\d{1,2})?$/',(string)$rate))fail('Enter a valid daily rate.');
    transaction(function()use($in,$email,$first,$last,$number,$rfid,$department,$position,$hired,$faculty,$rate,$rateDate){
        $d=one('SELECT * FROM departments WHERE id=?',[$department]);if(!$d)fail('Department not found.');$uid=0;
        if(!empty($in['id'])){$id=positive_id($in);$e=one('SELECT * FROM employees WHERE id=? FOR UPDATE',[$id]);if(!$e)fail('Employee not found.',404);$uid=(int)$e['user_id'];execute('UPDATE users SET email=?,branch_id=? WHERE id=?',[$email,$d['branch_id'],$uid]);execute('UPDATE employees SET employee_number=?,rfid_card_number=?,first_name=?,last_name=?,department_id=?,position_title=?,is_faculty=?,date_hired=? WHERE id=?',[$number,$rfid?:null,$first,$last,$department,$position,$faculty,$hired,$id]);}
        else{
            $existing=one('SELECT u.*,r.role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE email=? FOR UPDATE',[$email]);$role=one("SELECT id FROM roles WHERE role_name='employee'");
            if($existing){if($existing['role_name']!=='applicant'||!one("SELECT id FROM applications WHERE applicant_user_id=? AND status='accepted'",[$existing['id']]))fail('This email already belongs to an account. Only an accepted applicant can be appointed.',409);$uid=(int)$existing['id'];execute('UPDATE users SET role_id=?,branch_id=? WHERE id=?',[$role['id'],$d['branch_id'],$uid]);}
            else{$password=text($in,'password',1024);if(strlen($password)<12||strlen($password)>72)fail('Set an initial password between 12 and 72 bytes.');execute('INSERT INTO users(branch_id,role_id,username,email,password_hash) VALUES(?,?,?,?,?)',[$d['branch_id'],$role['id'],'e_'.bin2hex(random_bytes(12)),$email,password_hash($password,PASSWORD_DEFAULT)]);$uid=(int)db()->lastInsertId();}
            execute('INSERT INTO employees(user_id,employee_number,rfid_card_number,first_name,last_name,department_id,position_title,is_faculty,date_hired) VALUES(?,?,?,?,?,?,?,?,?)',[$uid,$number,$rfid?:null,$first,$last,$department,$position,$faculty,$hired]);$id=(int)db()->lastInsertId();
        }
        execute('INSERT INTO user_profiles(user_id,full_name) VALUES(?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name)',[$uid,$first.' '.$last]);
        $old=one('SELECT daily_rate FROM pay_rates WHERE employee_id=? AND effective_date=? ORDER BY id DESC LIMIT 1',[$id,$rateDate]);if(!$old||(float)$old['daily_rate']!==(float)$rate)execute('INSERT INTO pay_rates(employee_id,daily_rate,hourly_rate,effective_date) VALUES(?,?,?,?)',[$id,$rate,round((float)$rate/8,2),$rateDate]);
        audit('save_employee','employees',$id);
    });result(['success'=>true,'message'=>'Employee saved.']);
case 'profile':
    $u=require_user();result(['success'=>true,'record'=>one('SELECT full_name,contact_number,address FROM user_profiles WHERE user_id=?',[$u['id']])]);
case 'update_profile':
    $u=require_user();$phone=text($in,'contact_number',20,false);$address=text($in,'address',2000,false);$name=text($in,'full_name',150);
    execute('INSERT INTO user_profiles(user_id,full_name,contact_number,address) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),contact_number=VALUES(contact_number),address=VALUES(address)',[$u['id'],$name,$phone,$address]);audit('update_profile','user_profiles',(int)$u['id']);result(['success'=>true,'message'=>'Your contact details have been saved.']);
case 'employee_status':
    $u=require_user('hr_admin');$id=positive_id($in,'employee_id');$status=choice($in,'status',['active','on_leave','resigned','terminated']);$reason=text($in,'reason',5000);$date=date_value($in,'effective_date');if($date>date('Y-m-d'))fail('A status change cannot take effect in the future.');
    transaction(function()use($u,$id,$status,$reason,$date){$e=one('SELECT * FROM employees WHERE id=? FOR UPDATE',[$id]);if(!$e)fail('Employee not found.',404);execute('UPDATE employees SET current_status=? WHERE id=?',[$status,$id]);execute('INSERT INTO employee_status_histories(employee_id,previous_status,new_status,change_reason,changed_by_user_id,effective_date) VALUES(?,?,?,?,?,?)',[$id,$e['current_status'],$status,$reason,$u['id'],$date]);execute('UPDATE users SET is_active=?,session_version=session_version+1 WHERE id=?',[in_array($status,['resigned','terminated'],true)?0:1,$e['user_id']]);audit('change_status','employees',$id,$reason);});result(['success'=>true,'message'=>'Employment status saved.']);
case 'status_history':
    $u=require_user('hr_admin','employee');$where=$u['role']==='hr_admin'?'1=1':'e.user_id=?';result(['success'=>true,'records'=>rows("SELECT h.*,CONCAT(e.first_name,' ',e.last_name) employee FROM employee_status_histories h JOIN employees e ON e.id=h.employee_id WHERE $where ORDER BY h.id DESC",$u['role']==='hr_admin'?[]:[$u['id']])]);
case 'attendance':
    $u=require_user('hr_admin','employee');$where=$u['role']==='hr_admin'?'1=1':'e.user_id=?';result(['success'=>true,'records'=>rows("SELECT a.id,a.work_date,a.check_in_time,a.check_out_time,a.status,a.is_corrected,a.correction_notes,e.employee_number,CONCAT(e.first_name,' ',e.last_name) employee FROM attendance_logs a JOIN employees e ON e.id=a.employee_id WHERE $where ORDER BY a.work_date DESC,a.id DESC LIMIT 1000",$u['role']==='hr_admin'?[]:[$u['id']])]);
case 'scan':
    $u=require_user('employee');$e=employee($u);if($e['current_status']!=='active')fail('Attendance recording requires active employment.',403);$card=text($in,'rfid_card_number',50);if(!$e['rfid_card_number']||!hash_equals($e['rfid_card_number'],$card))fail('This RFID card does not match your employee record.',403);
    $date=date('Y-m-d');$time=date('Y-m-d H:i:s');$status=date('H:i:s')>setting('late_after',getenv('HRMS_LATE_AFTER') ?: '08:00:00')?'late':'present';
    $message=transaction(function()use($e,$date,$time,$status){
        one('SELECT id FROM employees WHERE id=? FOR UPDATE',[$e['id']]);
        $record=one('SELECT * FROM attendance_logs WHERE employee_id=? AND work_date=? FOR UPDATE',[$e['id'],$date]);
        if(!$record){execute('INSERT INTO attendance_logs(employee_id,branch_id,work_date,check_in_time,status) VALUES(?,?,?,?,?)',[$e['id'],$e['branch_id'],$date,$time,$status]);audit('check_in','attendance_logs',(int)db()->lastInsertId());return 'Check-in recorded at '.date('h:i A').'.';}
        if($record['check_out_time'])fail('You have already checked out today.',409);
        if(time()-strtotime($record['check_in_time'])<60)fail('Check-in was just recorded. Wait at least one minute before scanning to check out.',409);
        execute('UPDATE attendance_logs SET check_out_time=? WHERE id=?',[$time,$record['id']]);audit('check_out','attendance_logs',(int)$record['id']);return 'Check-out recorded at '.date('h:i A').'.';
    });result(['success'=>true,'message'=>$message]);
case 'correct_attendance':
    $u=require_user('hr_admin');$id=positive_id($in);$date=date_value($in,'work_date');$time=text($in,'check_in_time',5);$notes=text($in,'correction_notes',255);if(!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$time))fail('Enter a valid time.');if($date>date('Y-m-d'))fail('Attendance cannot be dated in the future.');
    $out=text($in,'check_out_time',5,false);
    if($out!==''&&!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$out))fail('Enter a valid check-out time.');
    if(strtotime($date.' '.$time)>time()||($out!==''&&(strtotime($date.' '.$out)>time()||$out<$time)))fail('Attendance times must be in the past and check-out must follow check-in.');
    transaction(function()use($u,$id,$date,$time,$out,$notes){$old=one('SELECT * FROM attendance_logs WHERE id=? FOR UPDATE',[$id]);if(!$old)fail('Attendance record not found.',404);execute("UPDATE attendance_logs SET work_date=?,check_in_time=?,check_out_time=?,status='corrected',is_corrected=1,corrected_by_user_id=?,correction_notes=? WHERE id=?",[$date,$date.' '.$time.':00',$out!==''?$date.' '.$out.':00':null,$u['id'],$notes,$id]);audit('correct_attendance','attendance_logs',$id,json_encode(['before'=>$old,'reason'=>$notes]));});result(['success'=>true,'message'=>'Attendance correction saved.']);
case 'payslips':
    $u=require_user('hr_admin','employee');$where=$u['role']==='hr_admin'?'1=1':'e.user_id=?';result(['success'=>true,'records'=>rows("SELECT p.*,CONCAT(e.first_name,' ',e.last_name) employee FROM payslips p JOIN employees e ON e.id=p.employee_id WHERE $where ORDER BY p.generated_at DESC,p.id DESC",$u['role']==='hr_admin'?[]:[$u['id']])]);
case 'generate_payslip':
    $u=require_user('hr_admin');$id=positive_id($in,'employee_id');$start=date_value($in,'period_start_date');$end=date_value($in,'period_end_date');if($start>$end||$end>date('Y-m-d'))fail('Use a completed pay period with its start before its end.');$deductions=(string)($in['deductions'] ?? '0');if(!preg_match('/^\d{1,9}(\.\d{1,2})?$/',$deductions))fail('Enter valid deductions.');
    transaction(function()use($u,$id,$start,$end,$deductions){if(!one('SELECT id FROM employees WHERE id=? FOR UPDATE',[$id]))fail('Employee not found.',404);if(one('SELECT id FROM payslips WHERE employee_id=? AND period_start_date<=? AND period_end_date>=? FOR UPDATE',[$id,$end,$start]))fail('A payslip already covers these dates.',409);
        $rate=one('SELECT daily_rate FROM pay_rates WHERE employee_id=? AND effective_date<=? ORDER BY effective_date DESC,id DESC LIMIT 1',[$id,$start]);if(!$rate)fail('Set a pay rate effective on or before this period first.');if(one('SELECT id FROM pay_rates WHERE employee_id=? AND effective_date>? AND effective_date<=?',[$id,$start,$end]))fail('This period includes a rate change. Generate separate payslips for each rate period.');
        $days=(int)one('SELECT COUNT(DISTINCT work_date) n FROM attendance_logs WHERE employee_id=? AND check_out_time IS NOT NULL AND work_date BETWEEN ? AND ?',[$id,$start,$end])['n'];if(!$days)fail('No completed attendance records were found for this pay period.');$cents=(int)round((float)$rate['daily_rate']*100);$gross=$days*$cents;$deduct=(int)round((float)$deductions*100);if($deduct>$gross)fail('Deductions cannot exceed gross salary.');
        execute('INSERT INTO payslips(payslip_number,employee_id,period_start_date,period_end_date,total_days_worked,daily_rate_applied,gross_salary,net_salary,generated_by_user_id,deductions) VALUES(?,?,?,?,?,?,?,?,?,?)',['PAY-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(5))),$id,$start,$end,$days,$rate['daily_rate'],$gross/100,($gross-$deduct)/100,$u['id'],$deductions]);audit('generate_payslip','payslips',(int)db()->lastInsertId());});result(['success'=>true,'message'=>'Payslip generated from recorded attendance.']);
case 'leaves':
    $u=require_user('hr_admin','employee');$where=$u['role']==='hr_admin'?'1=1':'e.user_id=?';result(['success'=>true,'records'=>rows("SELECT l.*,CONCAT(e.first_name,' ',e.last_name) employee FROM leave_requests l JOIN employees e ON e.id=l.employee_id WHERE $where ORDER BY l.id DESC",$u['role']==='hr_admin'?[]:[$u['id']])]);
case 'request_leave':
    $u=require_user('employee');$e=employee($u);if($e['current_status']!=='active')fail('Leave requests require active employment.',403);$start=date_value($in,'start_date');$end=date_value($in,'end_date');if($start>$end)fail('End date must be on or after start date.');execute('INSERT INTO leave_requests(employee_id,leave_type,start_date,end_date,reason) VALUES(?,?,?,?,?)',[$e['id'],text($in,'leave_type',50),$start,$end,text($in,'reason',10000)]);audit('request_leave','leave_requests',(int)db()->lastInsertId());result(['success'=>true,'message'=>'Leave request submitted.']);
case 'review_leave':
    $u=require_user('hr_admin');$id=positive_id($in);$status=choice($in,'status',['approved','rejected']);$notes=text($in,'review_notes',255,false);transaction(function()use($u,$id,$status,$notes){$leave=one('SELECT * FROM leave_requests WHERE id=? FOR UPDATE',[$id]);if(!$leave||$leave['status']!=='pending')fail('This request is no longer pending.',409);execute('UPDATE leave_requests SET status=?,reviewed_by_user_id=?,review_notes=? WHERE id=?',[$status,$u['id'],$notes,$id]);audit('review_leave','leave_requests',$id,$status);});result(['success'=>true,'message'=>'Leave request reviewed.']);
case 'students':
    $u=require_user('hr_admin','student');result(['success'=>true,'records'=>rows('SELECT s.*,u.email FROM students s JOIN users u ON u.id=s.user_id'.($u['role']==='hr_admin'?'':' WHERE s.user_id=?').' ORDER BY s.id DESC',$u['role']==='hr_admin'?[]:[$u['id']])]);
case 'review_student':
    $u=require_user('hr_admin');$id=positive_id($in);$status=choice($in,'status',['verified','rejected']);if(!one('SELECT id FROM students WHERE id=?',[$id]))fail('Student record not found.',404);execute('UPDATE students SET status=?,reviewed_by=?,review_notes=? WHERE id=?',[$status,$u['id'],text($in,'review_notes',255,false),$id]);audit('review_student','students',$id,$status);result(['success'=>true,'message'=>'Student verification saved.']);
case 'faculty':
    if(setting('academic_period')==='')fail('HR has not opened an evaluation period yet.',409);
    $u=require_user('student');$s=one("SELECT id FROM students WHERE user_id=? AND status='verified'",[$u['id']]);if(!$s)fail('HR must verify your student enrollment before you can evaluate faculty.',403);result(['success'=>true,'period'=>setting('academic_period'),'records'=>rows("SELECT e.id,e.first_name,e.last_name,d.department_name FROM employees e JOIN departments d ON d.id=e.department_id WHERE e.is_faculty=1 AND e.current_status='active' ORDER BY e.last_name")]);
case 'evaluate':
    $u=require_user('student');$s=one("SELECT id FROM students WHERE user_id=? AND status='verified'",[$u['id']]);if(!$s)fail('Student enrollment verification is required.',403);$id=positive_id($in,'employee_id');if(!one("SELECT id FROM employees WHERE id=? AND is_faculty=1 AND current_status='active'",[$id]))fail('Select an active faculty member.');
    $ratings=[];for($i=1;$i<=5;$i++){$r=positive_id($in,'rating_'.$i);if($r>5)fail('Ratings must be between 1 and 5.');$ratings[]=$r;}
    $period=setting('academic_period');if($period==='')fail('HR has not opened an evaluation period yet.',409);if(one('SELECT id FROM evaluations WHERE student_id=? AND employee_id=? AND academic_period=?',[$s['id'],$id,$period]))fail('You have already evaluated this faculty member for this academic period.',409);execute('INSERT INTO evaluations(student_id,employee_id,academic_period,ratings,average_rating,comments) VALUES(?,?,?,?,?,?)',[$s['id'],$id,$period,json_encode($ratings),array_sum($ratings)/5,text($in,'comments',10000,false)]);$id=(int)db()->lastInsertId();result(['success'=>true,'id'=>$id,'message'=>'Your faculty evaluation has been submitted.'],201);
case 'evaluations':
    $u=require_user('student');result(['success'=>true,'records'=>rows("SELECT v.id,v.academic_period,v.created_at,CONCAT(e.first_name,' ',e.last_name) faculty FROM evaluations v JOIN students s ON s.id=v.student_id JOIN employees e ON e.id=v.employee_id WHERE s.user_id=? ORDER BY v.id DESC",[$u['id']])]);
case 'reports':
    require_user('hr_admin');result(['success'=>true,'records'=>rows("SELECT CONCAT(e.first_name,' ',e.last_name) faculty,d.department_name,v.academic_period,COUNT(*) responses,ROUND(AVG(v.average_rating),2) average_rating FROM evaluations v JOIN employees e ON e.id=v.employee_id JOIN departments d ON d.id=e.department_id GROUP BY e.id,e.first_name,e.last_name,d.department_name,v.academic_period ORDER BY v.academic_period DESC,faculty")]);
case 'dashboard':
    require_user('hr_admin');$counts=[];foreach(['employees','applications','vacancies','students','attendance_logs'] as $table)$counts[$table]=(int)one("SELECT COUNT(*) n FROM `$table`")['n'];result(['success'=>true,'counts'=>$counts,'recent'=>rows('SELECT id,full_name,position_applied,status,created_at FROM applications ORDER BY id DESC LIMIT 5')]);
case 'audit':
    require_user('hr_admin');result(['success'=>true,'records'=>rows('SELECT a.id,u.email,a.action_type,a.target_table,a.record_id,a.created_at FROM audit_logs a JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 100')]);
default:fail('Endpoint not found.',404);
}
