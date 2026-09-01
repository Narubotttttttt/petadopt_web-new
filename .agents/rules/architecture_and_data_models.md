# Project Architecture & Database Design Rules

## 1. User Identity vs Domain Profile Separation
- **`users` table**: Strictly reserved for **authentication, roles, and verification** (`id`, `name`, `email`, `password`, `role`, `email_verified_at`, `remember_token`, `fcm_token`).
  - Do NOT add domain profile assets (such as `avatar`, `digital_signature_path`, address, phone, compliance status) to the `users` table.
- **`adopters_profile` table**: Handles all adopter-specific domain assets and records:
  - `adopter_code`, `full_name`, `avatar`, `phone`, `address`, `city`, `province`, `status` (`active`, `good_standing`, `restricted`, `blacklisted`), `admin_notes`, `digital_signature_path`, `last_check_in_date`.
- **`staff_profiles` table**: Handles all staff/admin domain assets and records:
  - `staff_code`, `full_name`, `avatar`, `position_title`, `phone`, `status`, `specialization`, `digital_signature_path`.

## 2. No Emojis
- Do not use emojis in code, comments, logs, or UI elements.
