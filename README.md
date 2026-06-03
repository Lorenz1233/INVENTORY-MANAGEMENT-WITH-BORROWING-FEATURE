# MSU-MCEST Campus Equipment Management System

A PHP and MySQL inventory borrowing system for MSU-MCEST. It manages campus equipment, materials, student and faculty accounts, borrow requests, approvals, returns, CSV imports, and audit tracking.

## Main Features

- Login, logout, registration, first-login password change, and forgot-password reset.
- Admin dashboard for inventory, borrow requests, transactions, users, students, courses, and settings.
- Student dashboard for browsing equipment, submitting borrow requests, and tracking request history.
- Faculty access for borrowing equipment and managing requests for items assigned to them.
- Equipment and materials management with searchable official owner assignment.
- "Added by" tracking for new equipment and material records.
- Borrow workflow with pending, approved, rejected, ongoing, returned, cancelled, and overdue states.
- CSV import for students, faculty, mixed masterlists, equipment, and materials.
- Automatic schema checks/backfills through the shared helper layer.
- Audit logging for important actions.

## Requirements

- XAMPP with Apache and MySQL/MariaDB
- PHP 7.4 or newer with PDO MySQL enabled
- MySQL database named `inventory_2`
- Browser internet access for Tailwind CSS CDN loading

## Local Setup

1. Copy the project folder into your XAMPP web directory:

   ```text
   C:\XAMPP\htdocs\xampp\inventory_borrowSystem-main
   ```

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Create the database in phpMyAdmin or MySQL:

   ```sql
   CREATE DATABASE inventory_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

4. Import the SQL backup:

   ```text
   inventory_2 (5)_no_triggers.sql
   ```

   If that file is not available, import:

   ```text
   inventory_2 (3).sql
   ```

5. Check the database connection in [db.php](db.php). The default XAMPP setup is:

   ```php
   $dbHost = '127.0.0.1';
   $dbName = 'inventory_2';
   $dbUser = 'root';
   $dbPass = '';
   ```

6. Open the system in a browser:

   ```text
   http://localhost/xampp/inventory_borrowSystem-main/
   ```

The app runs `ensure_system_schema()` through [process/_helpers.php](process/_helpers.php), so newer columns and support tables are created or backfilled when the system loads.

## User Roles

### Admin

Admins can manage the full system:

- Equipment and materials
- Students and officials
- User approvals and roles
- Courses, categories, units, and settings
- Borrow requests and transactions
- CSV imports

### Faculty

Faculty can:

- Borrow equipment
- Browse available equipment
- View their own borrow requests
- Manage borrow requests for equipment/materials assigned to their official ID
- Access admin-style inventory/request pages where allowed

Faculty cannot manage restricted administrator-only user role actions.

### Student

Students can:

- Browse available equipment
- Submit borrow requests
- Track request status
- Change password

## Account Flow

### Login

Users sign in with their ID number and password from [pages/login.php](pages/login.php).

If `is_default_password = 1`, the user is redirected to a password-change page before accessing the system.

### Registration

Students and faculty can request an account from [pages/register.php](pages/register.php). New account requests may require admin approval before login.

### Forgot Password

Users can reset their password from [pages/forgot-password.php](pages/forgot-password.php) by entering:

- ID number
- Registered last name
- New password
- Confirm new password

The account must be active and approved.

## Equipment And Materials

Equipment and material records are stored in the `items` table.

When adding or editing an item:

- Select the item category and unit.
- Select an owner from the officials masterlist.
- Add one or more owner allocations and enter the quantity owned by each official.
- Use the owner search field to quickly filter officials by name or ID.
- The system records the user who created or updated the item.

Owner assignment is required for both manual entry and CSV import. The owner must exist in `officials_masterlist`.

The same item name and category can have more than one owner. For example, if Mary owns 20 laptops and Jake owns 20 laptops, add both owner allocations under Laptop. Borrowers will see one Laptop card, then choose Mary or Jake in the request form. The catalog tables show each owner quantity and the combined total availability.

## Borrow Workflow

1. Student or faculty borrower submits a request.
2. Request is linked to the borrower user and the item owner.
3. Admin can see all requests.
4. Faculty can see and manage requests for items assigned to their official ID.
5. Approved requests become transactions.
6. Returned items update available quantity and stock status.

Inventory quantity checks prevent approving or saving invalid stock values.

## CSV Import

CSV imports are handled in [pages/admin-settings.php](pages/admin-settings.php) and [process/admin_settings.php](process/admin_settings.php).

Available import types:

- Students
- Faculty
- Mixed masterlist
- Equipment
- Materials

Sample CSV files are in:

```text
assets/samples/
```

Equipment CSV required fields:

```text
item_name or equipment_name or name
quantity or qty
owner_official_id
```

Recommended equipment headers:

```text
item_name,item_code,category,unit,quantity,condition,description,date_added,owner_official_id
```

Materials CSV required fields:

```text
material_name or item_name
quantity
owner_official_id
```

Recommended materials headers:

```text
material_name,category,unit,quantity,unit_price,description,date_added,owner_official_id
```

Important CSV rules:

- `owner_official_id` must match an official in `officials_masterlist`.
- Repeat the same equipment/material name with a different `owner_official_id` to split ownership.
- `quantity` must be a whole number and cannot be negative.
- `unit_price` accepts up to two decimal places.
- `date_added` should use `YYYY-MM-DD` when provided.
- Safe import mode skips failed rows and continues.
- Strict import mode rolls back the whole file if any row fails.

## Project Structure

```text
/
|-- assets/
|   |-- images/                 Logo and image assets
|   `-- samples/                CSV import examples
|-- css/
|   `-- app.css                 Shared application styling
|-- js/
|   `-- shared.js               Shared UI behavior
|-- pages/                      Browser-facing PHP pages
|-- partials/                   Shared page helpers and layout partials
|-- process/                    Form handlers and backend workflow logic
|-- db.php                      PDO database connection
|-- index.php                   Redirect entry point
|-- inventory_2 (3).sql         Database backup
|-- inventory_2 (5)_no_triggers.sql
`-- README.md
```

## Important Files

- [process/_helpers.php](process/_helpers.php) - shared validation, auth guards, schema setup, audit logging, inventory helpers.
- [process/login.php](process/login.php) - login and first-password-change routing.
- [process/register.php](process/register.php) - student/faculty account requests.
- [process/forgot_password.php](process/forgot_password.php) - forgot-password reset.
- [process/equipment.php](process/equipment.php) - equipment create/update/delete.
- [process/materials.php](process/materials.php) - materials create/update/delete.
- [process/requests.php](process/requests.php) - request approval/rejection/cancellation.
- [process/transactions.php](process/transactions.php) - transaction and return handling.
- [partials/page-data.php](partials/page-data.php) - shared page query helpers and current-user display data.

## Testing Checklist

Use this quick checklist after code or database changes:

1. Start Apache and MySQL.
2. Open the login page.
3. Test login as admin, student, and faculty.
4. Add equipment and confirm the owner dropdown lists officials.
5. Search for an owner while adding equipment/materials.
6. Confirm the item shows who added it.
7. Submit a borrow request as student.
8. Submit a borrow request as faculty.
9. Approve/reject a request as admin.
10. Approve/reject an owned-item request as faculty.
11. Return an approved item and confirm stock updates.
12. Test forgot-password reset.
13. Import a sample CSV in safe mode.
14. Import an invalid CSV row and confirm the error summary is clear.

Developer lint check:

```powershell
$php = 'C:\xampp\php\php.exe'
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & $php -l $_.FullName }
```

Schema bootstrap check:

```powershell
C:\xampp\php\php.exe -r "require 'process/_helpers.php'; echo 'SCHEMA_BOOTSTRAP_OK';"
```

## Troubleshooting

### Database connection failed

Check that MySQL is running and that [db.php](db.php) matches your database username, password, and database name.

### Tailwind styles do not load

This system loads Tailwind from a CDN. Make sure the browser has internet access.

### Login redirects to change password

That means `is_default_password = 1`. Change the password once, then sign in normally.

### Owner dropdown is empty

Add officials first or import the faculty/official masterlist. Equipment and materials require an owner from `officials_masterlist`.

### Faculty cannot see a request

Faculty request management is owner-based. The item must be assigned to the faculty member's `official_id`.

### CSV import fails

Check the selected dataset, required headers, date format, and `owner_official_id` values. Equipment and materials cannot be imported without a valid owner official.
