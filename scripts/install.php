<?php
// Additive schema updates only. Run from the project directory: php scripts/install.php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/api/bootstrap.php';
$pdo=db();
function column(string $table,string $name,string $definition): void {if(!one('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',[$table,$name]))execute("ALTER TABLE `$table` ADD COLUMN `$name` $definition");}
execute("INSERT INTO roles(role_name,description) SELECT 'student','Student evaluation access' WHERE NOT EXISTS(SELECT 1 FROM roles WHERE role_name='student')");
column('vacancies','category',"ENUM('teaching','non-teaching') NOT NULL DEFAULT 'non-teaching'");
column('payslips','deductions','DECIMAL(10,2) NOT NULL DEFAULT 0');
column('attendance_logs','check_out_time','TIMESTAMP NULL DEFAULT NULL');
column('users','session_version','INT NOT NULL DEFAULT 1');
execute("CREATE TABLE IF NOT EXISTS user_profiles(user_id BIGINT PRIMARY KEY, full_name VARCHAR(150) NOT NULL, contact_number VARCHAR(20) NULL, address TEXT NULL, FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
execute("INSERT INTO user_profiles(user_id,full_name) SELECT e.user_id,CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE NOT EXISTS(SELECT 1 FROM user_profiles p WHERE p.user_id=e.user_id)");
execute("INSERT INTO user_profiles(user_id,full_name,contact_number) SELECT a.applicant_user_id,a.full_name,a.contact_number FROM applications a WHERE a.id=(SELECT MAX(a2.id) FROM applications a2 WHERE a2.applicant_user_id=a.applicant_user_id) AND NOT EXISTS(SELECT 1 FROM user_profiles p WHERE p.user_id=a.applicant_user_id)");
execute("CREATE TABLE IF NOT EXISTS students(id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id BIGINT NOT NULL UNIQUE,student_number VARCHAR(50) NOT NULL UNIQUE,full_name VARCHAR(150) NOT NULL,program VARCHAR(150) NOT NULL,year_level TINYINT NOT NULL,status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',reviewed_by BIGINT NULL,review_notes VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id),FOREIGN KEY(reviewed_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
execute("CREATE TABLE IF NOT EXISTS evaluations(id BIGINT AUTO_INCREMENT PRIMARY KEY,student_id BIGINT NOT NULL,employee_id BIGINT NOT NULL,academic_period VARCHAR(30) NOT NULL,ratings TEXT NOT NULL,average_rating DECIMAL(3,2) NOT NULL,comments TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY one_response(student_id,employee_id,academic_period),FOREIGN KEY(student_id) REFERENCES students(id),FOREIGN KEY(employee_id) REFERENCES employees(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
execute("CREATE TABLE IF NOT EXISTS login_attempts(id BIGINT AUTO_INCREMENT PRIMARY KEY,attempt_key VARCHAR(64) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,KEY attempt_window(attempt_key,created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
execute("CREATE TABLE IF NOT EXISTS system_settings(setting_key VARCHAR(50) PRIMARY KEY,setting_value VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// Stop MySQL from changing the original check-in time when HR corrects another field.
execute('ALTER TABLE attendance_logs MODIFY check_in_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
echo 'Schema updated. Existing users and records were preserved.',PHP_EOL;
