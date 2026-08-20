# GUVI Internship — Register / Login / Profile

Flow: **Register → Login → Profile**

## Folder structure
```
assets/
css/style.css
js/
  register.js
  login.js
  profile.js
php/
  config.php     (edit your DB/Redis/Mongo credentials here)
  db.php         (MySQL PDO connection)
  redis.php      (Redis connection + session-token helpers)
  mongo.php      (MongoDB connection + profile read/write)
  response.php   (shared JSON helpers)
  register.php
  login.php
  profile.php
  logout.php
sql/schema.sql
index.html
register.html
login.html
profile.html
```

## How each requirement is met
- **HTML/JS/CSS/PHP fully separated**: every page is plain HTML, styling is in `css/style.css`, all logic is in `js/*.js`, and all server code is in `php/*.php`. No inline `<script>`/`<style>`/PHP mixed into markup.
- **jQuery AJAX only, no form submission**: pages use `<div>` wrappers, not `<form>` tags. Every request goes through `$.ajax()`.
- **Bootstrap + responsive**: Bootstrap 5 (via CDN) handles layout/responsiveness; `style.css` only adds light custom styling.
- **MySQL for registered data**: `users` table (`sql/schema.sql`), accessed only through PDO **prepared statements** (see `db.php`, `register.php`, `login.php`).
- **MongoDB for profile details**: age/dob/contact/address/bio are stored as a document per user in MongoDB (`mongo.php`, keyed by MySQL `user_id`).
- **Session via browser localStorage, not PHP session**: login returns an opaque token; the browser stores it in `localStorage` and sends it back as `Authorization: Bearer <token>` on every profile request. `session_start()` is never used.
- **Redis backs the session**: `login.php` stores `session:<token> -> user_id` in Redis with a TTL; `profile.php`/`logout.php` validate/destroy it there.

## Requirements on the server
- PHP 8+ with extensions: `pdo_mysql`, `redis` (phpredis), `mongodb`
- MySQL 5.7+/8
- Redis server
- MongoDB server

## Setup
1. `mysql -u root -p < sql/schema.sql`
2. Edit `php/config.php` with your MySQL / Redis / MongoDB connection details.
3. Make sure `redis-server` and `mongod` are running.
4. Serve the project root with PHP's built-in server for local testing:
   ```
   php -S localhost:8000
   ```
5. Open `http://localhost:8000/register.html`, create an account, then log in — you'll land on `profile.html`.

## Deploying to Render
This repo includes everything needed for a one-click Blueprint deploy:
- `Dockerfile` — builds the PHP/Apache app (with pdo_mysql, redis, mongodb extensions)
- `Dockerfile.mysql` — MySQL 8 image that auto-loads `sql/schema.sql` on first boot
- `docker/entrypoint.sh` — makes Apache listen on Render's dynamic `$PORT`
- `render.yaml` — Blueprint wiring up the web app, MySQL, MongoDB, and Redis

Steps:
1. Push this project to a GitHub (or GitLab/Bitbucket) repo.
2. In the Render dashboard: **New +** → **Blueprint**, and select that repo.
3. Render reads `render.yaml` and proposes 4 services (web app, MySQL, MongoDB, Redis) — click **Apply**.
4. Wait for all 4 to finish deploying (MySQL/MongoDB take longest, since they build fresh disks).
5. Visit `https://<your-service-name>.onrender.com/register.html`.

**Cost note:** MySQL and MongoDB run as Render private services, which have no free tier and need a paid plan + persistent disk (`render.yaml` defaults them to the `starter` plan). The web app and Redis can stay on Render's free tier. Check render.com/pricing for current rates before deploying.

If you'd rather avoid paying for two private services, swap `MONGO_HOST`/`MONGO_PORT` for a `MONGO_URI` env var pointing at a free MongoDB Atlas cluster, and/or point `DB_HOST`/`DB_USER`/`DB_PASS` at a free external MySQL host — `config.php` already supports both without code changes.

## Notes
- `mongo.php` uses the native `ext-mongodb` driver directly (`MongoDB\Driver\Manager`), so no Composer install is required. If you prefer the `mongodb/mongodb` Composer package's Collection API instead, only `mongo.php` needs to change — nothing else depends on it.
- Passwords are hashed with `password_hash()` (bcrypt) and checked with `password_verify()`.
