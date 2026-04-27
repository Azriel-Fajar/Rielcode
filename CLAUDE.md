# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Rielcode is a PHP-based web development agency website for selling web development services. It runs on a traditional LAMP stack (XAMPP locally, cPanel in production).

- **Local URL**: `http://localhost/Rielcode/`
- **Production**: `https://rielcode.com/`

## Development Setup

**Prerequisites**: XAMPP (Apache + MySQL + PHP) must be running.

**Install PHP dependencies**:
```bash
composer install
```

**No build step** — this is a server-rendered PHP application with no bundler, transpiler, or asset pipeline.

## Technology Stack

- **Backend**: PHP (procedural, no framework)
- **Database**: MySQL via `mysqli` and `PDO`
- **Frontend**: HTML5, Bootstrap 5.3.8, vanilla JavaScript
- **PDF generation**: DomPDF (via Composer)
- **Email**: PHPMailer (`/PHPMailer/`)
- **AI chatbot**: OpenAI API (proxied through `/proxy.php`)

## Configuration

- `/config.php` — local DB credentials and API keys
- `/smtp_config.php` — SMTP credentials for PHPMailer (local vs. production switch)
- `/connection.php` — MySQL connection wrapper
- `/.htaccess` — Apache URL rewriting (clean URLs / routing)

Production config overrides the local one: the app loads `/home/rier5192/config.php` first and falls back to `/config.php`. Never commit real credentials.

## Architecture

### Routing
URL routing is handled entirely by `.htaccess` Apache rewrite rules. There is no PHP router — each page is a standalone `.php` file.

### Page Structure
Each page includes shared components:
- `/navbar.php` — navigation bar
- `/footer.php` — footer
- `/inc/seo.php` — SEO meta tags

### Key Entry Points

| Path | Purpose |
|------|---------|
| `/index.php` | Homepage (hero, packages, estimator) |
| `/about.php` | About page (mission & team) |
| `/package.php` | Service package tiers (Student/Starter/Pro/Premium) |
| `/projects.php` | Portfolio |
| `/requirement.php` | Project requirements intake form |
| `/order-form/index.php` | Order form (main service ordering UI) |
| `/checkout/index.php` | Checkout/payment flow |
| `/terms&conditions/index.php` | Terms & conditions page |
| `/admin.php` | Admin dashboard (chat logs, orders, packages) |
| `/admin_login.php` | Admin authentication |
| `/admin_logout.php` | Admin session logout |
| `/proxy.php` | OpenAI API proxy endpoint (POST, CORS-enabled) |

### Admin Panel
Protected by PHP session-based authentication. Access via `/admin_login.php`. The dashboard (`/admin.php`) allows viewing and editing database tables (orders, packages, chat logs) through `/admin_edit.php`.

### Chatbot
`/JS/chatbot.js` (842 lines) handles the AI chatbot widget. It POSTs messages to `/proxy.php`, which forwards requests to OpenAI and returns responses. CORS is configured for `rielcode.com` and localhost.

### Invoice Generation
PDF invoices are generated server-side using DomPDF and stored in `/invoices/`. The DomPDF library lives in both `/vendor/` (Composer) and `/dompdf/` (manual copy).

### Frontend Assets
- `/CSS/` — 15 CSS files, one per major section/component
- `/JS/` — `main.js` (core init), `chatbot.js`, `order.js`, `checkout.js`
- `/IMG/` — images and branding

## Database
MySQL. Connection is established in `/connection.php`. Admin panel uses PDO for table operations; most other pages use `mysqli`. No ORM or query builder.