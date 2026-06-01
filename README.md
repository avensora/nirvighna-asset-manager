# Nirvighna

**An Avensora Product** — Simple business management for small businesses.

## Vision

Help business owners stay organized, track money properly, generate invoices, communicate with team members, and maintain a complete history of business activities. Not an ERP — a focused, practical tool.

---

## V1 Scope (In Development)

| Feature | Status |
|---|---|
| Authentication (register, login, verify email, forgot/reset password) | Complete |
| Dashboard (revenue, expenses, profit, pending invoices, activity, events) | Complete |
| Client Management (CRUD) | Complete |
| Invoice Management (PDF, email, paid/unpaid) | Complete |
| Income & Expense Tracking | Complete |
| Calendar (meetings, deadlines, reminders) | Complete |
| Activity Logs (who, what, when) | Complete |
| Team Members (Manager / Team Member roles) | Complete |
| Team Chat (internal messaging + file sharing) | Complete |
| Error Pages (401, 403, 404, 500) | Complete |

---

## Tech Stack

- **Framework:** Laravel 11 (PHP 8.4)
- **Database:** MySQL (XAMPP)
- **Frontend:** Boron Admin Template (Bootstrap 5, ApexCharts, FullCalendar)
- **PDF:** barryvdh/laravel-dompdf
- **Activity Logging:** spatie/laravel-activitylog
- **Chat:** HTTP short polling (3-second interval)
- **Mail (dev):** Mailpit (localhost:1025)
- **Mail (prod):** Brevo SMTP

---

## Local Development

**URL:** http://nirvighna.test

**Requirements:**
- XAMPP running (Apache + MySQL)
- PHP 8.4 (via Herd)

**Setup:**
```bash
cd P:\xampp\htdocs\Projects\personal\Laravel\nirvighna
composer install
php artisan migrate
php artisan db:seed
```

**Seeded accounts:**
| Email | Password | Role |
|---|---|---|
| admin@nirvighna.test | password | Manager |
| team@nirvighna.test | password | Team Member |

---

## Roles

| Role | Access |
|---|---|
| **Manager** | Full access to all features including team management and activity log |
| **Team Member** | Access to areas assigned by manager (invoices, chat, calendar, clients) |

---

## Postponed (Not V1)

- Project-based product management
- Advanced project tracking (timelines, task management, client portals)
- Advanced reporting and budget planning
- Asset management

---

## Important Rule

Keep Nirvighna focused. If a feature does not directly help with managing money, invoices, clients, team communication, or business activities — evaluate carefully before adding it.

**This README is the source of truth.** Update it whenever scope changes.
