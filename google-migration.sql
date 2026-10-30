ALTER TABLE users
  ADD COLUMN google_sub VARCHAR(64) NULL,
  ADD UNIQUE KEY uq_users_google_sub (google_sub);
-- Also verify: users.branch_id must allow NULL, and applicant_role_id in google-config.php exists in `roles`.
-- ALTER TABLE users MODIFY branch_id INT NULL;   -- only if it is currently NOT NULL
