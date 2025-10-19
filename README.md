# student-enrollment-system-php
Full-stack php, mysql, database, authentication, sessions, enrollment-system, concurrency-control, transactions, crud, gpa, academic-system, stored-procedure, MySQL schema with trigger, view
# Web-Based Student Enrollment System (CMSC 4003/5043 – Applications Database Systems)

This repository contains my course project for Applications Database Systems. It combines:
- Part 1: Web-based user management with login/logout, session control, access control, and admin CRUD
- Part 2: A student enrollment information system with academic info, multi-section enrollment, prerequisite and deadline checks, concurrency control, and admin functions (student CRUD and grade entry with probation handling)

Tech stack:
- PHP (procedural), MySQL, HTML/CSS
- SQL objects: at least one VIEW, one STORED PROCEDURE, and one TRIGGER
- Concurrency: MySQL transactions with row-level locking to avoid long-duration transactions and ensure consistency

## Features (mapped to requirements)

Student
- Personal info page: ID, name, age, address, type (UG/Grad), probation status, username
- Academic info page:
  - Completed courses count, total credits, GPA
  - GPA = SUM(course_grade × course_credits) / SUM(course_credits)
  - Sections taken and in-progress with: section ID, course number, title, semester, credits, grade (if completed)
- Enrollment page:
  - Search sections by semester and/or partial course number
  - View section details: ID, course#, title, credits, semester, date/time, enrollment deadline, capacity, available seats
  - Enroll into multiple sections simultaneously with checks:
    1) Deadline not passed
    2) Prerequisites satisfied
    3) Seats available
    4) Course not already completed
  - Clear success/failure result messages (with reason when failed)
  - Concurrency control for simultaneous enrollments

Admin
- Add new student (auto-generated student ID with format: XX123456 using max sequence + 1)
- Concurrency-safe ID generation for simultaneous admins
- List/search students by name/id/course#/type/probation (substring searches supported)
- Update or delete selected student
- Enter grades; automatically place/remove probation based on GPA threshold (2.0)

Implementation (per Part 2 requirements)
- Includes at least one: VIEW, STORED PROCEDURE, TRIGGER (see sql/login_schema.sql)
- Concurrency control implemented without long-duration transactions
- Clear, intuitive web UI

## Project structure

```
.
├─ admin.php, admin_add.php, admin_add_action.php, admin_update*.php, admin_delete*.php
├─ student.php, student_personalinformation.php, student_academicinformation.php
├─ enrollment_page.php, enroll_action.php
├─ grade_entry.php, grade_entry_action.php
├─ login.html, login_action.php, logout_action.php, change_password.php
├─ student_* (CRUD and dashboards), welcomepage.php
├─ utility_functions.php
├─ config.example.php            # Copy to config.php and set DB credentials
├─ sql/
│  └─ login_schema.sql          # Tables + VIEW + PROCEDURE + TRIGGER (+ optional seed data)
├─ docs/                         # EER diagram and screenshots (add later)
└─ .github/workflows/php-lint.yml
```

Note: Some filenames may be abbreviated above. See repo root for full list.

## Local setup (5 minutes)

Prerequisites:
- PHP 8+ and MySQL 8+ (or XAMPP/MAMP/WAMP)
- A web browser

1) Create database and import schema
- Create a database (e.g., `student_enrollment`)
- Import the schema:
  - Using CLI: `mysql -u <user> -p < sql/login_schema.sql`
  - Or use phpMyAdmin and import the file
- Ensure the schema includes:
  - Tables for users, sessions, students, courses, sections, prerequisites, enrollments, grades
  - VIEW, STORED PROCEDURE, and TRIGGER per Part 2 requirements
  - Optional seed data for demo accounts

2) App configuration
- Copy `config.example.php` to `config.php`
- Set host, port, DB name, user, and password
- Ensure `config.php` is NOT committed to Git (it is in `.gitignore`)

3) Run the app
Option A: PHP built‑in server (fastest for demo)
```
php -S localhost:8000 -t .
```
Open http://localhost:8000/login.html

Option B: XAMPP/MAMP
- Place the repo in the web root (e.g., `htdocs`)
- Start Apache and MySQL
- Visit `http://localhost/<folder>/login.html`

4) Demo accounts (example)
- Admin: username: admin_user / password: ****
- Student: username: student_user / password: ****
Update with actual credentials you seeded.

## Concurrency control

- Enrollment: Uses transactions with row-level locking around seat counts and prerequisite checks to ensure consistency under simultaneous requests
- ID generation: Uses a transaction and locking around the sequence derivation (max + 1) to prevent duplicates

Add a brief “how it works” note in code comments and here if you want to highlight implementation details for interviews.

## GPA and probation

- GPA = SUM(course_grade × credit_hours) / SUM(credit_hours)
- Probation status auto-updates via grade entry logic and/or a TRIGGER when GPA drops below 2.0 or recovers to ≥ 2.0

## Screenshots and diagram (add in docs/)

- docs/eer.png — EER diagram for both Part 1 and Part 2
- docs/*.png — Screens: login, student dashboard, enrollment search, admin add student, grade entry, etc.

## Notes

- This repository includes only non-sensitive code and schema. Database credentials are in an untracked `config.php`.
- For large datasets or media, use Git LFS.

## License

MIT (see LICENSE)

## Author

- Name: Your Name
- Course: CMSC 4003/5043 — Applications Database Systems
- Term: Fall 2024 (or your actual term)
