-- Optional but recommended: the database itself refuses a second application
-- by the same applicant for the same vacancy (the PHP page already checks too).
-- Run once in phpMyAdmin on smart_hrms. If it fails with "Duplicate entry",
-- delete the duplicate test rows in `applications` first.
ALTER TABLE applications
  ADD UNIQUE KEY uq_applicant_vacancy (applicant_user_id, vacancy_id);
