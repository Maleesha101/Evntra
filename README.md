# Evntra

Evntra is a university competition management platform for students, organizers, and administrators. It brings together competition discovery, registration, approvals, conflict detection, notifications, analytics, and calendar scheduling in one place.

Built with **PHP 8.1+**, **MySQL 8.0+**, **vanilla JavaScript**, and **custom CSS**.

---

## Features

- Public landing page with featured competitions
- Student registration, login, password reset, bookmarks, and competition browsing
- Student dashboard showing active and previous registered events
- Organizer competition creation, editing, registration management, and analytics
- Admin competition approval, user management, and conflict reporting
- Calendar view using FullCalendar.js with multi-day event support
- JSON APIs for search, bookmarks, registrations, notifications, and conflict checks
- CSRF protection, prepared statements, password hashing, and login rate limiting
- Banner uploads with file type and size validation
- Notification bell with unread dot (clears on mark-all-read via AJAX)
- Responsive layout with sidebar navigation and mobile bottom navigation

---

## Setup

### 1. Clone the project

```bash
git clone https://github.com/Maleesha101/Evntra.git
cd Evntra
```

### 2. Create the database

Using XAMPP MySQL (or any MySQL 8.0+ client):

```bash
mysql -u root -e "CREATE DATABASE evntra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root evntra < sql/schema.sql
```

### 3. Configure environment (optional)

Create a `.env` file in the project root to override defaults:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=evntra
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8000
```

> Without a `.env` file the app uses the defaults above — it will work out of the box with XAMPP.

### 4. Email support (optional)

Email (password reset, registration confirmations) uses **PHPMailer** if installed, otherwise falls back to PHP's built-in `mail()`. To install PHPMailer:

```bash
composer install
```

If you skip this step the app still works — emails just use the system `mail()` function.

### 5. Make uploads writable

```bash
mkdir -p uploads/banners
```

### 6. Start the server

```bash
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

---

## Default Logins

Seed data is included in `sql/schema.sql` for quick testing:

| Role | Email | Password |
|---|---|---|
| Student | `student@evntra.test` | `Student123!` |
| Organizer | `organizer@evntra.test` | `Organizer123!` |
| Admin | `admin@evntra.test` | `Admin123!` |

---

## Folder Structure

```
Evntra/
├── index.php              # Public landing page
├── config/
│   └── db.php             # PDO database connection
├── auth/                  # Login, register, logout, password reset
├── student/               # Browse, competition detail, dashboard, bookmarks
├── organizer/             # Dashboard, create/edit competitions, analytics
├── admin/                 # Approvals, user management, conflict report
├── api/                   # JSON endpoints (search, bookmarks, register, etc.)
├── includes/              # Shared functions, header, footer, navbar, mailer
├── assets/
│   ├── css/               # main.css, dashboard.css, calendar.css
│   ├── js/                # calendar.js, search-filter.js, analytics.js, etc.
│   └── img/               # logo.svg
├── uploads/
│   └── banners/           # Uploaded competition banner images
└── sql/
    └── schema.sql         # Database schema + seed data
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1+ |
| Database | MySQL 8.0+ via PDO |
| Frontend | Vanilla HTML / CSS / JavaScript |
| Calendar | FullCalendar.js 6.x |
| Charts | Chart.js |
| Email | PHPMailer 6.x (optional) |
| Icons | Google Material Symbols |
| Fonts | Google Fonts (Space Grotesk, Inter) |

---

## License

MIT
