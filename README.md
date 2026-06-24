# Evntra

Evntra is a centralized university competition management platform for students, organizers, and administrators. It brings together competition discovery, registration, approvals, conflict detection, notifications, analytics, and calendar scheduling in one place so that universities can manage events without spreadsheets or fragmented communication.

The application is built with PHP 8.1+, MySQL 8.0+, vanilla JavaScript, and custom CSS. It includes a role-based auth system, a public landing page, searchable competition listings, organizer workflows for creating and editing events, admin moderation, Chart.js analytics, FullCalendar.js scheduling, PHPMailer-powered messaging, and a responsive dark-accented interface designed for desktop and mobile.

## Features

- Public landing page with featured competitions
- Student registration, login, password reset, dashboards, bookmarks, and competition browsing
- Organizer competition creation, edit flow, registration management, analytics, and conflict warnings
- Admin competition approval, user management, and conflict reporting
- JSON APIs for browse search, bookmarks, registrations, notifications, calendars, and conflict checks
- CSRF protection, prepared statements, password hashing, and login rate limiting
- Banner uploads with MIME and size validation
- Notification bell with unread count and recent notifications dropdown
- Responsive layout with sidebar navigation and mobile bottom navigation

## Setup

1. Clone or open the project folder.
2. Import the database schema from `sql/schema.sql` into MySQL 8.0+.
3. Create a MySQL database named `unicompete_hub` or update the credentials in `.env`.
4. Configure environment variables or a local `.env` file:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=evntra
DB_USER=root
DB_PASS=
APP_URL=http://localhost/evntra
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=your-smtp-user
SMTP_PASS=your-smtp-password
SMTP_ENCRYPTION=tls
MAIL_FROM=no-reply@evntra.test
MAIL_FROM_NAME=Evntra
```

5. Install the PHP dependency for email support:

```bash
composer install
```

6. Make sure `uploads/banners/` is writable by the web server.
7. Start PHP through your preferred local web server and browse to `/index.php`.

## Default Logins

Seed data is included in the schema for quick testing:

- Student: `student@evntra.test` / `Student123!`
- Organizer: `organizer@evntra.test` / `Organizer123!`
- Admin: `admin@evntra.test` / `Admin123!`

## Folder Structure

- `index.php` - public landing page
- `config/db.php` - PDO bootstrap
- `auth/` - login, registration, logout, and password reset flows
- `student/` - browsing, competition detail, registrations, and bookmarks
- `organizer/` - dashboards, competition creation/editing, analytics, and registration management
- `admin/` - approvals, user management, and conflict reporting
- `api/` - JSON endpoints for front-end interactivity and form submission
- `includes/` - shared layout, auth guard, helper functions, and mail wrapper
- `assets/css/` - custom styling for the public UI and dashboards
- `assets/js/` - live search, calendar, analytics, conflict checks, and shared UI behavior
- `uploads/banners/` - competition banner uploads
- `sql/schema.sql` - schema and demo seed data

## Screenshots

Placeholder for production screenshots of:

- Public landing page
- Student browse and calendar view
- Organizer dashboard and analytics
- Admin approval and conflict report pages

## License

MIT
