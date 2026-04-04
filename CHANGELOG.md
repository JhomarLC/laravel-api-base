# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

This project follows tags **`vMAJOR.MINOR.PATCH`** (for example **`v1.0.0`**).

## [Unreleased]

<!-- New changes go here before the next tagged release. -->

## [1.0.0] — 2026-03-29

_Git tag: `v1.0.0`_

### Added

- **Docker / Sail**
  - `compose.yaml` stack: PHP app service (`laravel`), **MySQL 8.4**, persistent volume `sail-mysql`, and **Adminer** (default UI port `8080`, override with `ADMINER_PORT`).
  - App container: `restart: unless-stopped`, `extra_hosts` for `host.docker.internal`, bind mount with **`:cached`** (better macOS file sync), healthcheck (`php artisan --version`), memory limits under `deploy.resources`.
  - MySQL healthcheck via `mysqladmin ping`.
  - Named bridge network `sail` and volume `sail-mysql`.
- **Auth API (Sanctum)** — routes under `api/auth`:
  - `POST register` — creates user, sends email verification OTP (no token until verified).
  - `POST login` — returns API token only if email is verified; throttling on sensitive routes where applied.
  - `POST email/verify` — verify email with OTP (`throttle:5,1`).
  - `POST email/resend` — resend OTP (`throttle:3,1`).
  - `POST password/forgot` — password reset link (`throttle:3,1`).
  - `POST password/reset` — complete reset (`throttle:5,1`).
  - `GET me` — current user (auth:sanctum).
  - `POST logout` / `POST logout-all` — revoke current or all tokens.
- **Form requests**: `LoginRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `VerifyEmailOtpRequest`, `ResendOtpRequest`.
- **Email verification (OTP)**: `EmailVerificationService`, `EmailVerificationOtp` model, migration `email_verification_otps` (email, hashed OTP, expiry).
- **Notifications**: `VerifyEmailOtpnotification` (OTP mail), `ResetPasswordNotification` (frontend reset URL + optional Blade template).
- **Mail templates (Blade)**: `resources/views/emails/auth/password-reset.blade.php`, `resources/views/emails/auth/verify-email.blade.php`.
- **Debug-only mail previews** in `routes/web.php` when `APP_DEBUG=true`: `/mail/preview/password-reset`, `/mail/preview/verify-email`.
- **Config**
  - `config('app.frontend_url')` from `FRONTEND_URL` (password reset links).
  - `config/auth.php`: `password_reset_email_view`, `verify_email_otp_view` (env: `PASSWORD_RESET_EMAIL_VIEW`, `VERIFY_EMAIL_OTP_VIEW`).
- **`.env.example`**
  - `APP_PORT`, `APP_SERVICE`, `FRONTEND_URL`, mail template env keys.
  - Default `MAIL_MAILER=log` for local dev without an SMTP catcher (see Notes).
- **`.gitignore`**: ignore `.DS_Store`.
- **Project docs**: this changelog file.

### Changed

- **Compose service name**: default app service renamed from `laravel.test` to **`laravel`**. Set `APP_SERVICE=laravel` in `.env` so Sail commands (`sail artisan`, etc.) target the correct container.
- **`AuthController`**: constructor-injected `EmailVerificationService`; expanded responses and JSON shapes; uses `Password` facade for reset flow.
- **`User` model**: implements `MustVerifyEmail`; overrides `sendEmailVerificationNotification` and `sendPasswordResetNotification`.
- **`UserResource`**: includes `email_verified_at` (ISO string) and `has_verified_email`.
- **`routes/api.php`**: removed default `/user` closure; auth grouped under `prefix('auth')` with middleware as above.
- **`.env.example`**: `MAIL_MAILER` default set to `log` for simpler local runs without Mailpit.

### Fixed / developer experience

- Documented **`APP_PORT`** (e.g. `8000`) so the app matches Sail’s port wiring when you want `http://localhost:8000` instead of Sail’s default host port `80`.
- **`APP_SERVICE=laravel`** avoids Sail error `service "laravel.test" is not running` when the stack uses the `laravel` service name.

### Notes

- **Mail / Mailpit**: If you set `MAIL_MAILER=smtp` and `MAIL_HOST=mailpit`, the stack must include a **Mailpit** (or compatible) service named `mailpit` on the same Docker network, or DNS resolution will fail inside the app container. Alternatively keep `MAIL_MAILER=log` for development or add an `axllent/mailpit` service to `compose.yaml` (as in Laravel Sail’s `mailpit` stub) and expose `8025` for the web UI.
- **Adminer**: use server **`mysql`**, database/user/password from `.env` (`DB_*`), when connecting from the Adminer container.

[Unreleased]: https://github.com/JhomarLC/laravel-api-base/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/JhomarLC/laravel-api-base/releases/tag/v1.0.0
