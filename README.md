# Football Quiz (PHP + MySQL)

This is a small university quiz system built with plain PHP, MySQL (mysqli), and vanilla JS.

## What’s in here

- Public pages: login/register/quiz/scoreboard
- Admin dashboard: CRUD for quizzes and users + scoreboard view
- Backend endpoints in `class/`:
  - `class/action.php` – form actions (login/register/admin CRUD)
  - `class/getQuiz.php` – returns quiz questions as JSON
  - `class/postQuizResult.php` – stores a user’s quiz result

> Note: there is also a duplicated copy of the public pages under `quiz/`.

## Setup (local)

### 1) Create the database

Import either:

- `data/quizsystem.sql` (recommended)

### 2) Configure DB connection

The connection defaults are in `class/db_connect.php`.

You can override them with environment variables:

- `QUIZ_DB_HOST` (default `127.0.0.1`)
- `QUIZ_DB_PORT` (default `3306`)
- `QUIZ_DB_USER` (default `root`)
- `QUIZ_DB_PASS` (default empty)
- `QUIZ_DB_NAME` (default `quiz_database`)

If you import `data/quizsystem.sql`, you’ll likely want:

The SQL dump header mentions `quizsystem`, but it does **not** include `CREATE DATABASE` or `USE`, so you can import it into any DB name you choose.

Two valid setups:

1) Keep the app defaults: create/import into `quiz_database` and keep `QUIZ_DB_NAME` unset.
2) Use the DB name shown in the dump header: create/import `quizsystem` and set:

- `QUIZ_DB_NAME=quizsystem`
- `QUIZ_DB_PORT=3306` (or whatever your MySQL uses)

### 3) Run the app

Serve the project with a PHP-capable web server (Apache in XAMPP/WAMP/Laragon is typical).

Entry points:

- `/index.php`
- `/admin/login.php`

## Improvements applied (no new UI/features)

These changes keep the same pages and flows, but make them safer and more reliable.

### Security & correctness

- **SQL injection fixed**: DB queries that used string concatenation were converted to prepared statements.
- **Password hashing**: passwords are now stored with `password_hash()` and verified with `password_verify()`.
  - Existing plaintext passwords remain usable: on successful login, they are automatically upgraded to a hash.
- **Trustworthy scoreboard**: the client no longer posts `correctCount/wrongCount` directly.
  - The browser submits the user’s selected answers, and the server computes correct/wrong/incomplete.
- **Reduced XSS risk**: user-visible DB values are escaped with `htmlspecialchars()` in scoreboards and admin lists.
- **Redirects terminate execution**: `header('Location: ...')` now uses `exit;` where needed.

### Public hosting hardening

- **CSRF protection** for state-changing actions:
  - Login/register/admin create+update forms include a hidden `csrf_token`.
  - Admin deletes and logout links include `&csrf=...`.
  - Quiz result submission includes `csrf_token` in the JSON body.
- **Safer session defaults**:
  - Strict session mode + HTTP-only cookies.
  - `SameSite=Lax`.
  - `Secure` cookie flag is enabled automatically when HTTPS is detected.

### API behavior

- `class/getQuiz.php` no longer returns the correct answer to the browser.
- `class/postQuizResult.php` now expects:

```json
{
  "answers": [
    { "quizId": 1, "selectedOption": "France" },
    { "quizId": 2, "selectedOption": null }
  ],
  "csrf_token": "..."
}
```

and responds with:

```json
{
  "status": "success",
  "correctCount": 3,
  "wrongCount": 7,
  "incompleteCount": 0
}
```

## Notes

- The project still uses comma-separated options in the DB (`quizzes.options`). For a larger system you’d typically normalize this, but it’s kept as-is to avoid changing features or schema.
- The duplicated `quiz/` directory is kept for compatibility; consider removing it later to avoid drift.

## If you post publicly

- Use HTTPS (so session cookies can use the `Secure` flag).
- Set DB credentials via environment variables instead of committing secrets.
- Change/remove the default admin credentials from the sample SQL dumps.

See `DEPLOYMENT.md` for step-by-step deployment (including Proxmox) and local performance testing.
