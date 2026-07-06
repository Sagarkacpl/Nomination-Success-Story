# PHP OOP Auth System (Register + Login)

1. Zero-dependency, plain-PHP OOP starter for register/login with security
   best practices baked in. No Composer required (uses a tiny custom
   autoloader).

## Folder structure

```
php-auth-system/
├── .htaccess                 # ROOT rewrite: hides "public" from every URL
├── autoload.php              # tiny PSR-4-style class autoloader
├── app/                      # business logic — NOT web-accessible directly
│   ├── Config/ (config.php, Database.php)
│   ├── Core/ (Controller.php, Model.php, Session.php)
│   ├── Traits/ (FlashMessageTrait.php)
│   ├── Helpers/ (SmtpMailer.php, FileUploader.php, Validator.php)
│   ├── Models/ (User.php)
│   └── Controllers/ (AuthController.php)
├── views/
│   ├── partials/ (header.php, footer.php, flash.php)
│   └── auth/ (register.php, login.php)
├── public/                   # actual document root on the server
│   ├── bootstrap.php         # shared setup included by every entry file
│   ├── index.php             # THIS IS THE LOGIN PAGE (root URL)
│   ├── register.php
│   ├── logout.php
│   ├── verify.php
│   ├── dashboard.php
│   ├── .htaccess             # hides .php extension
│   └── assets/css/style.css
├── uploads/profile_pics/
└── database/schema.sql
```

## How the URLs work (no "public", no ".php")

Two `.htaccess` files work together:

1. **`php-auth-system/.htaccess`** (project root) — silently forwards every
   request into `public/`, but the URL bar never shows `public/`. Real files
   (uploads, css) are served directly and skip the rewrite.
2. **`public/.htaccess`** — hides the `.php` extension, so `register.php`
   is reached by simply visiting `register`.

Result:

| You visit                             | What actually runs              |
| ------------------------------------- | ------------------------------- |
| `http://localhost/php-auth-system/`   | `public/index.php` (login page) |
| `.../php-auth-system/register`        | `public/register.php`           |
| `.../php-auth-system/dashboard`       | `public/dashboard.php`          |
| `.../php-auth-system/logout`          | `public/logout.php`             |
| `.../php-auth-system/verify?token=..` | `public/verify.php`             |

**Important:** Apache's document root must still physically point at
`php-auth-system/` (e.g. your XAMPP `htdocs/php-auth-system/`), NOT at
`public/`. The root `.htaccess` does the redirecting internally — that's
what makes `public` disappear from the URL. Also make sure
`AllowOverride All` is enabled for this directory in your Apache config,
otherwise `.htaccess` rules are ignored.

## Setup

1. Import `database/schema.sql` into MySQL.
2. Edit `app/Config/config.php`:
   - `APP_URL` → your actual base URL, e.g. `http://localhost/php-auth-system` (no `/public`, no trailing slash)
   - `DB_*` constants
   - `SMTP_*` constants (Gmail needs an "App Password", not your login password)
3. Make sure `uploads/profile_pics/` is writable by PHP.
4. Visit `http://localhost/php-auth-system/` → login page.

## Security features included

- **Password hashing** via `password_hash()`/`password_verify()`.
- **CSRF protection** on every POST form.
- **Prepared statements everywhere** (PDO) — no SQL injection.
- **Session hardening** — `httponly`, `samesite=Lax`, ID regeneration on login.
- **Brute-force protection** — failed logins tracked in `login_attempts`, temporary lockout.
- **Email verification** — tokenized link required before first login.
- **Secure file upload** — real MIME check via `finfo`, extension whitelist, size limit, random filename, `getimagesize()` sanity check.
- **Output escaping** — `htmlspecialchars()` everywhere in views.
- **Security headers** — `X-Content-Type-Options`, `X-Frame-Options`, basic CSP (`public/bootstrap.php`).

## Extending

- Add `password_resets` table (commented in `schema.sql`) + a `forgot-password.php` entry file for "forgot password".
- Swap `SmtpMailer` for PHPMailer later if needed — nothing else changes since callers only use `->send($to, $subject, $body)`.
