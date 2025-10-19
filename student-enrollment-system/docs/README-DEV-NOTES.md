# Developer Notes

- Course specs are in `docs/proj1.txt` and `docs/proj2.txt`.
- Schema (including view, stored procedure, trigger, and concurrency control patterns) is in `sql/login_schema.sql`.

DB connection
- Centralize connection in `config.php` and use `getDbConnection()` across scripts.
- Load secrets via environment variables (`.env` not committed).

Concurrency control (per spec)
- Use transactions + appropriate isolation/locking around:
  - Auto student ID generation (read max sequence, insert next) — ensure atomicity.
  - Enrollment seat checks — recheck availability inside the same transaction.

Security
- Use prepared statements everywhere.
- Hash passwords with `password_hash()` and verify with `password_verify()`.
- Consider CSRF tokens on forms that mutate data.

Local run tips
- Quick: `php -S localhost:8000` at project root.
- XAMPP/WAMP users: put files under web root and configure DB in `.env`.

CI
- PHP lint runs on push/PR. Add PHPUnit later if you introduce tests.