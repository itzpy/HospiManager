# Hospi Manager — Deployment Guide
**Target:** InfinityFree (free hosting) + hospimanager.to domain + Brevo SMTP email

---

## Step 1 — Sign Up for InfinityFree
1. Go to **infinityfree.com** and create a free account.
2. Create a new hosting account (you get a subdomain like `hospimanager.rf.gd` initially).
3. Note your **FTP hostname**, **FTP username**, and **FTP password** from the control panel.

---

## Step 2 — Set Up the Database
1. In InfinityFree cPanel → **MySQL Databases**:
   - Create a new database (e.g., `epiz_XXXXX_hospi`)
   - Create a database user with a strong password
   - Assign the user to the database (all privileges)
2. Go to **phpMyAdmin** in cPanel.
3. Select your new database and click **Import**.
4. Upload `db/hospital_management.sql` — this creates all tables with sample categories.

---

## Step 3 — Configure Environment
1. **Copy** `config/env.php` (already in the project).
2. Fill in your production values:
   ```php
   define('APP_ENV', 'production');
   define('APP_URL', 'https://hospimanager.to');

   define('DB_HOST', 'sql123.infinityfree.com'); // from cPanel
   define('DB_USER', 'epiz_XXXXX_youruser');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'epiz_XXXXX_hospi');

   define('SMTP_HOST', 'smtp-relay.brevo.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your_brevo_login@email.com');
   define('SMTP_PASS', 'your_brevo_smtp_key');
   define('MAIL_FROM', 'no-reply@hospimanager.to');
   define('MAIL_FROM_NAME', 'Hospi Manager');
   ```
3. **Never commit this file** — it is in `.gitignore`.

---

## Step 4 — Set Up Brevo SMTP (Free Email)
1. Sign up at **brevo.com** (free plan = 300 emails/day).
2. Go to **SMTP & API** → **SMTP** tab.
3. Copy your **SMTP login** (email) and **SMTP key** (password).
4. Add a sender domain: go to **Senders & IP** → **Domains** → add `hospimanager.to`.
5. Follow Brevo's DNS verification steps (add TXT/CNAME records to your domain registrar).
6. Paste the SMTP credentials into `config/env.php` above.

---

## Step 5 — Upload Files via FTP
Use **FileZilla** (free) or any FTP client:
1. Connect with your InfinityFree FTP credentials.
2. Upload **all project files** to the `/htdocs/` directory on the server.
   - Include: `vendor/` folder (contains PHPMailer)
   - **Do NOT upload**: `composer.phar`, `.git/`
3. After uploading, go to your cPanel and **set `config/env.php`** with production values (or edit it via the File Manager in cPanel).

---

## Step 6 — Point Your Domain
In your domain registrar (wherever you bought `hospimanager.to`):

**Option A — Nameservers (recommended):**
Change nameservers to InfinityFree's nameservers (shown in cPanel → Domains).

**Option B — A Record:**
Add an A record pointing `hospimanager.to` → InfinityFree's server IP (shown in cPanel).

DNS propagation takes 10–60 minutes.

---

## Step 7 — Enable Free SSL
1. In InfinityFree cPanel → **SSL Certificates** → request a free SSL for `hospimanager.to`.
2. This uses Let's Encrypt. It may take a few minutes to activate.
3. Your site will be live at `https://hospimanager.to`.

---

## Step 8 — Create Your First Superadmin
After deploy, the database has no users. Create your superadmin:

**Option A — Via Register page** (easiest):
1. Go to `https://hospimanager.to/view/register.php`
2. Register an account (it will be created as `staff` role by default).
3. Go to phpMyAdmin → `users` table → edit that row → change `role` to `superadmin`.

**Option B — SQL directly in phpMyAdmin**:
```sql
INSERT INTO users (first_name, last_name, email, password, role)
VALUES ('Your', 'Name', 'you@email.com', '$2y$10$HASH', 'superadmin');
```
Generate the password hash at: `https://bcrypt-generator.com/` (cost factor 10).

---

## Step 9 — Verify Everything
- [ ] Visit `https://hospimanager.to` — landing page loads
- [ ] Login works and redirects to correct dashboard by role
- [ ] Inventory CRUD works (add item, edit, delete)
- [ ] Forgot password sends a real email
- [ ] Attempting `https://hospimanager.to/actions/login_user.php` returns 403 Forbidden
- [ ] Attempting `https://hospimanager.to/db/hospital_management.sql` returns 403 Forbidden

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank white page | Check PHP error log in cPanel → `logs/` or enable `display_errors` temporarily in `env.php` by changing `APP_ENV` to `development` |
| DB connection error | Double-check DB_HOST in cPanel — InfinityFree uses a specific hostname like `sql123.infinityfree.com` |
| Email not sending | Verify Brevo domain is verified; check SMTP credentials |
| 500 on .htaccess | InfinityFree supports Apache mod_rewrite — if errors occur, remove the HTTPS redirect lines from `.htaccess` temporarily |
| Session not persisting | Make sure `config/session.php` is being auto-prepended via `.htaccess` |
