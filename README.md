# MSU-MCEST Campus Equipment Management System

Simple PHP + MySQL inventory and borrowing system for a school department.
The pages use Tailwind CSS (CDN), vanilla JavaScript, and separate PHP process files.

## Folder structure

```
/                           -> index.php / index.html (redirects to login)
/db.php                     -> PDO database connection for inventory_2
/process/                   -> separate PHP form handlers
/pages/                     -> PHP application pages plus HTML redirects
/js/shared.js               -> shared logic (sidebar, modal, tabs, fade)
/js/<page>.js               -> optional page-specific JS
/assets/images/             -> logo / icons (placeholder)
/partials/                  -> suggested PHP includes location
                              (sidebar-admin.php, sidebar-student.php,
                               topbar.php, head.php, footer.php)
```

## How to use locally
Run the folder through XAMPP/Apache so PHP can execute, then open `index.php`.
Tailwind is loaded via CDN.

## PHP integration notes
- Dashboard cards, filters, tabs, and tables read live data from MySQL.
- Forms use `method`, proper `name` attributes, and PHP handlers under `/process/`.
- Database connection settings live in `db.php` and target the `inventory_2` database.
- Login uses sessions and sends first-login users to the change-password page when `is_default_password = 1`.
- Admin-side handlers call `require_admin()`; borrower-side request submission calls `require_borrower()`.
- `process/admin_settings.php` supports CSV import for students, faculty, mixed masterlists, equipment, and materials.
- `process/categories.php`, `process/units.php`, and `process/masterlist.php` cover the extra management flows.
- Equipment, materials, masterlist, courses, requests, transactions, users, categories, and units use simple CRUD handlers.
- Empty states appear only when the database query returns no records.

## Backend consistency rules
- All handlers use PDO prepared statements through the shared helper layer.
- Multi-table writes use transactions and roll back on failure.
- Masterlist saves automatically create or synchronize login users.
- Categories and units are resolved case-insensitively and duplicate names are merged or reused.
- Inventory writes reject negative quantities, duplicate item names within a category, and deletes with borrowing history.
- Borrow approvals and returns lock affected rows and recalculate `items.stock_status`.
- CSV imports validate headers, report total/success/failed counts, and support safe mode or strict rollback mode.
- Important actions are written to `audit_log`.

## Pages
- Public: `login.php`, `register.php`
- Admin: `admin-dashboard.php`, `equipment.php`, `materials.php`, `students.php`,
         `users.php`, `requests.php`, `transactions.php`, `admin-settings.php`,
         `change-password.php`
- Student: `student-dashboard.php`, `student-browse.php`, `student-requests.php`,
           `student-change-password.php`

## Color tokens (Tailwind config inline)
- Primary navy:   #0B2545
- Navy dark:      #061A33
- Accent gold:    #D4A017
- Accent gold-2:  #B8860B
- Surface:        #F7F8FB
- Border:         #E5E7EB
