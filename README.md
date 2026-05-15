# Evntra

Evntra is a centralized university competition management platform for students, organizers, and administrators. It brings together competition discovery, registration, approvals, conflict detection, notifications, analytics, and calendar scheduling in one place so that universities can manage events without spreadsheets or fragmented communication.

The application is built with PHP 8.1+, MySQL 8.0+, vanilla JavaScript, and custom CSS. It includes a role-based auth system, a public landing page, searchable competition listings, organizer workflows for creating and editing events, admin moderation, Chart.js analytics, FullCalendar.js scheduling, PHPMailer-powered messaging, and a responsive dark-accented interface designed for desktop and mobile.

## Quick Links

- 📖 **[Frontend Documentation](FRONTEND.md)** - Complete guide to the frontend architecture, components, and development
- 🎨 **[Component Style Guide](STYLEGUIDE.html)** - Interactive showcase of all UI components
- 🚀 **[Quick Start](#setup)** - Installation and setup instructions
- 📁 **[Folder Structure](#folder-structure)** - Project organization and file descriptions

## Features

### Frontend
- ✅ Modern & minimalist HTML/CSS/JavaScript interface (no frameworks)
- ✅ Single-page routing system with client-side navigation
- ✅ Responsive design (desktop, tablet, mobile)
- ✅ Landing page with hero section and features showcase
- ✅ Complete authentication flow (login, register, password recovery)
- ✅ Student dashboard with registrations, bookmarks, and competition browse
- ✅ Organizer dashboard with competition creation and management
- ✅ Admin dashboard with user management and approval workflows
- ✅ Advanced search, filtering, pagination, and sorting
- ✅ Conflict detection and scheduling system
- ✅ Export functionality (CSV/JSON)
- ✅ Accessibility-compliant components
- ✅ Full documentation and style guide

### Backend (PHP + MySQL)
- Public landing page with featured competitions
- Student registration, login, password reset, dashboards, bookmarks, and competition browsing
- Organizer competition creation, edit flow, registration management, analytics, and conflict warnings
- Admin competition approval, user management, and conflict reporting
- JSON APIs for browse search, bookmarks, registrations, notifications, calendars, and conflict checks
- CSRF protection, prepared statements, password hashing, and login rate limiting
- Banner uploads with MIME and size validation
- Notification bell with unread count and recent notifications dropdown
- Responsive layout with sidebar navigation and mobile bottom navigation

## Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with CSS variables and grid/flexbox
- **JavaScript (ES6+)** - Vanilla JS with no framework dependencies
- **Routing** - Custom client-side router with query parameters
- **API Communication** - Fetch API with JSON payloads

### Backend
- **PHP 8.1+** - Server-side logic and API endpoints
- **MySQL 8.0+** - Relational database
- **PHPMailer** - Email delivery for password recovery and notifications
- **Chart.js** - Analytics visualizations
- **FullCalendar.js** - Competition scheduling calendar

## Setup

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Modern web browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- Composer (for PHP dependencies)

### Installation Steps

1. Clone or open the project folder in your web server root (e.g., `/var/www/html/evntra`).

2. Import the database schema:
```bash
mysql -u root -p < sql/schema.sql
```

3. Create a `.env` file in the project root with your configuration:

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

4. Install PHP dependencies:
```bash
composer install
```

5. Ensure `uploads/banners/` directory is writable:
```bash
chmod 755 uploads/banners/
```

6. Start your local web server and navigate to the project:

```bash
# Using PHP built-in server
php -S localhost:8000

# Or use Apache/Nginx
# Then visit: http://localhost/evntra/
```

## Default Logins

Seed data is included in the schema for quick testing:

- Student: `student@evntra.test` / `Student123!`
- Organizer: `organizer@evntra.test` / `Organizer123!`
- Admin: `admin@evntra.test` / `Admin123!`

## Folder Structure

### Frontend Files
- `index.html` - Landing page with hero section and features
- `assets/css/` - Comprehensive styling
  - `main.css` - Global styles, components, and responsive design
  - `landing.css` - Landing page specific styles
  - `dashboard.css` - Dashboard layouts
  - `calendar.css` - Calendar styling
- `assets/js/` - Client-side JavaScript
  - `api.js` - API integration layer and helper functions
  - `main.js` - Routing system and page handlers
  - `utils.js` - Search, filter, pagination, sorting, export utilities
  - `analytics.js` - Analytics functionality
  - `conflict-checker.js` - Conflict detection
- `student/` - Student-facing pages (HTML)
  - `browse.html` - Browse competitions
  - `my-registrations.html` - View registrations
  - `bookmarks.html` - Bookmarked competitions
  - `dashboard.html` - Student dashboard
- `organizer/` - Organizer-facing pages (HTML)
  - `create-competition.html` - Create new competition
  - `manage-registrations.html` - Manage participants
  - `my-competitions.html` - View own competitions
  - `edit-competition.html` - Edit competition
  - `analytics.html` - Competition analytics
  - `dashboard.html` - Organizer dashboard
- `admin/` - Admin-facing pages (HTML)
  - `manage-users.html` - User management
  - `approve-competitions.html` - Competition approval
  - `dashboard.html` - Admin dashboard
  - `conflict-report.html` - View conflict reports
- `FRONTEND.md` - Complete frontend documentation
- `STYLEGUIDE.html` - Interactive component showcase

### Backend Files
- `index.php` - Landing page router
- `config/db.php` - Database connection bootstrap
- `auth/` - Authentication flows
  - `login.php` - User login
  - `register.php` - User registration
  - `logout.php` - User logout
  - `forgot-password.php` - Password recovery request
  - `reset-password.php` - Password reset
- `student/` - Student PHP pages
  - `browse.php` - Browse and search competitions
  - `dashboard.php` - Student dashboard
  - `my-registrations.php` - View registrations
  - `bookmarks.php` - View bookmarks
  - `competition.php` - Competition details
  - `register-event.php` - Register for competition
- `organizer/` - Organizer PHP pages
  - `dashboard.php` - Organizer dashboard
  - `create-competition.php` - Create competition
  - `my-competitions.php` - View own competitions
  - `edit-competition.php` - Edit competition
  - `manage-registrations.php` - Manage participants
  - `analytics.php` - View analytics
- `admin/` - Admin PHP pages
  - `dashboard.php` - Admin dashboard
  - `manage-users.php` - Manage users
  - `approve-competitions.php` - Approve competitions
  - `conflict-report.php` - View conflict reports
- `api/` - JSON API endpoints
  - `competitions.php` - Competition operations
  - `register.php` - Registration operations
  - `bookmark.php` - Bookmark operations
  - `notifications.php` - Notification operations
  - `check-conflict.php` - Conflict checking
- `includes/` - Shared components
  - `header.php` - Page header
  - `navbar.php` - Navigation bar
  - `footer.php` - Page footer
  - `auth-guard.php` - Authentication middleware
  - `functions.php` - Helper functions
  - `mailer.php` - Email wrapper
- `uploads/banners/` - Competition banner uploads
- `sql/schema.sql` - Database schema and seed data
- `vendor/` - Composer dependencies (PHPMailer)

## Frontend Usage

The frontend is a single-page application (SPA) with client-side routing. No build process is required - simply serve the files with your web server.

### Accessing Pages

All pages are accessed through the main `index.html` with query parameters:
- Home: `/` or `/?page=landing`
- Login: `/?page=login`
- Register: `/?page=register`
- Browse Competitions: `/?page=browse`
- Student Dashboard: `/?page=student-dashboard`
- Organizer Dashboard: `/?page=organizer-dashboard`
- Admin Dashboard: `/?page=admin-dashboard`

### Navigation

Navigation happens through:
1. Clicking links with `class="nav-link"` (uses `navigateTo()`)
2. Directly calling `window.navigateTo('page-name')`
3. Using query parameters: `?page=page-name`

### Component Library

All reusable UI components are in `assets/css/main.css`:
- **Buttons**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-success`, `.btn-danger`
- **Cards**: `.card`, `.card-header`, `.card-body`, `.card-footer`
- **Forms**: `.form-group`, `.form-control`, `.btn-submit`
- **Grids**: `.grid-2`, `.grid-3`, `.grid-4` (responsive)
- **Alerts**: `.alert`, `.alert-info`, `.alert-success`, `.alert-warning`, `.alert-danger`
- **Badges**: `.badge`, `.badge-primary`, `.badge-success`, etc.

See `STYLEGUIDE.html` for interactive component showcase.

### CSS Customization

Edit CSS variables in `assets/css/main.css` to customize colors:

```css
:root {
  --primary: #3498db;        /* Main action color */
  --secondary: #2c3e50;      /* Secondary elements */
  --success: #2ecc71;        /* Success states */
  --warning: #f39c12;        /* Warning states */
  --danger: #e74c3c;         /* Error states */
  --light: #ecf0f1;          /* Light backgrounds */
  --lighter: #f8f9fa;        /* Lighter backgrounds */
  --dark: #2c3e50;           /* Dark text */
}
```

### JavaScript Modules

- **`assets/js/api.js`**: API wrapper with error handling
- **`assets/js/main.js`**: Router and page handlers
- **`assets/js/utils.js`**: Search, filter, pagination, sorting, export utilities

## API Integration

The frontend communicates with the PHP backend through JSON APIs in the `/api` directory.

### Base API Configuration

Edit the API base URL in `assets/js/api.js` if needed:

```javascript
const API_BASE_URL = '/api';  // Change this for different deployment paths
```

### Available Endpoints

- `GET /api/competitions.php` - List competitions with filters
- `POST /api/register.php` - Register for competition
- `POST /api/bookmark.php` - Add/remove bookmarks
- `GET /api/notifications.php` - Get user notifications
- `POST /api/check-conflict.php` - Check time conflicts

All endpoints return JSON with `success` boolean and optional `error` or `data` fields.

### Authentication

Authentication is token-based using localStorage:

```javascript
// Login stores user data and token
Storage.setUser(userData);      // Stores in localStorage.user
Storage.setToken(token);        // Stores in localStorage.token

// Check if user is logged in
if (API.Auth.isAuthenticated()) { ... }

// Get current user
const user = Storage.getUser();
```

All API requests automatically include the token in headers:

```javascript
Authorization: Bearer <token>
```

## Development

### Adding a New Page

1. Create an HTML file in the appropriate folder (`student/`, `organizer/`, `admin/`)
2. Register the route in `assets/js/main.js`:

```javascript
Router.register('page-name', async () => {
  const html = await fetch('path/to/page.html').then(r => r.text());
  document.body.innerHTML = html;
  // Initialize event listeners here
});
```

3. Add navigation link in the appropriate template
4. Test with `?page=page-name`

### Adding a New API Endpoint

1. Create the endpoint in `/api/endpoint.php`
2. Add wrapper in `assets/js/api.js`:

```javascript
API.newFeature = {
  async getData() {
    return await fetch(`${API_BASE_URL}/endpoint.php`)
      .then(r => r.json())
      .catch(e => console.error(e));
  }
};
```

3. Use in your page handler

### Code Style

- Use ES6+ features (async/await, arrow functions, template literals)
- Wrap API calls in try-catch
- Always validate user input before sending to API
- Use semantic HTML5 elements
- Follow CSS BEM naming convention for classes

## Troubleshooting

### Frontend Issues

**Page not loading when navigating**
- Check browser console for errors (F12 → Console tab)
- Verify route name matches registered routes in `assets/js/main.js`
- Check that `?page=` parameter is correctly formatted

**API calls failing with 404**
- Verify API endpoint exists in `/api/` folder
- Check `API_BASE_URL` in `assets/js/api.js` matches your deployment path
- Ensure PHP backend is running and accessible

**Login not working**
- Check localStorage in browser DevTools (F12 → Application → Local Storage)
- Verify user credentials are correct
- Check PHP backend for authentication errors in server logs

**Styles not loading**
- Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
- Check Network tab in DevTools for 404 errors on CSS files
- Verify `assets/css/` path is correct relative to deployed location

**Responsive design not working on mobile**
- Check that `<meta name="viewport">` is in index.html head
- Test in actual mobile device or use DevTools device emulation
- Check CSS media queries in `assets/css/main.css`

### Backend Integration

**CORS errors when calling API**
- Add CORS headers to PHP backend response:
  ```php
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
  header('Content-Type: application/json');
  ```

**Session lost after page refresh**
- Ensure localStorage is not being cleared
- Check that token is being properly stored: `console.log(localStorage.token)`
- Verify browser allows localStorage (not in private/incognito mode)

**Form data not being saved**
- Check Network tab to see actual request payload
- Verify API endpoint is returning success response
- Check PHP backend logs for validation errors

## Screenshots

Placeholder for production screenshots of:

- Public landing page
- Student browse and calendar view
- Organizer dashboard and analytics
- Admin approval and conflict report pages

## License

MIT
