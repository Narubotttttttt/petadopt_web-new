# Workspace Guidelines & System Architecture

## Database & Model Boundaries

### 1. `users` Table (Authentication & Verification Only)
- **Role & Scope:** Exclusively for credentials, authentication, account verification, and role assignment (`id`, `name`, `email`, `password`, `role`, `email_verified_at`, `remember_token`, `fcm_token`).
- **Strict Boundary:** Never store domain profile assets (avatars, digital signatures, phone numbers, home addresses, or compliance/ban statuses) in the `users` table.

### 2. Domain Profiles (`adopters_profile` & `staff_profiles`)
- **`adopters_profile`:** Manages all adopter information (`avatar`, `phone`, `address`, `city`, `province`, `status`, `admin_notes`, `digital_signature_path`, `last_check_in_date`).
- **`staff_profiles`:** Manages all staff/admin information (`avatar`, `position_title`, `phone`, `specialization`, `digital_signature_path`, `status`).

---

## Coding Standards
- **Zero Emojis:** Do not include emojis in code, documentation, logs, or UI text.
