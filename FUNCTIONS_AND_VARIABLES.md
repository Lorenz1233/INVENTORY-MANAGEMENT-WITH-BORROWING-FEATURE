# Function and Variable Labels

This file labels the main functions and variables used in this project. Line numbers refer to the current files in this folder.

## Function Labels

| File:line | Function | Where used | How used |
| --- | --- | --- | --- |
| `js/shared.js:31` | `highlightActiveNav()` | `js/shared.js:22` | Finds the current page name and marks the matching sidebar link as active. |
| `js/shared.js:40` | `bindSidebarToggle()` | `js/shared.js:23` | Connects the mobile sidebar button to the sidebar open/close class. |
| `js/shared.js:48` | `bindModals()` | `js/shared.js:24` | Opens and closes modal dialogs using `data-modal-open` and `data-modal-close`. |
| `js/shared.js:71` | `bindTabs()` | `js/shared.js:25` | Filters table rows by tab status. |
| `js/shared.js:91` | `bindTableSearch()` | `js/shared.js:26` | Filters table/list rows based on search input text. |
| `js/shared.js:105` | `bindConfirmActions()` | `js/shared.js:27` | Shows browser confirmation before destructive actions. |
| `partials/page-data.php:4` | `h($value)` | Most page files | Escapes text before outputting it in HTML. |
| `partials/page-data.php:9` | `js($value)` | `pages/admin-settings.php:229` | Encodes PHP strings for safe use inside JavaScript attributes/calls. |
| `partials/page-data.php:14` | `money($value)` | `pages/materials.php` | Formats material prices and values with two decimals. |
| `partials/page-data.php:19` | `one_value($sql, $params)` | Dashboards and counters | Runs a query and returns one column, usually for counts. |
| `partials/page-data.php:27` | `all_rows($sql, $params)` | Most page files | Runs a query and returns all rows for tables, cards, and dropdowns. |
| `partials/page-data.php:35` | `current_user()` | `partials/page-data.php:132` | Loads the logged-in user record for page headers and permission checks. |
| `partials/page-data.php:51` | `user_full_name($user)` | `partials/page-data.php:133` | Builds the display name from first/last name, falling back to username. |
| `partials/page-data.php:57` | `initials($name)` | `partials/page-data.php:134` | Builds the initials shown in the profile badge. |
| `partials/page-data.php:69` | `pending_request_count()` | `partials/page-data.php:137` | Counts pending borrow requests for sidebar badges. |
| `partials/page-data.php:74` | `material_condition($itemAlias, $categoryAlias)` | `pages/materials.php`, `partials/page-data.php:81` | Builds the SQL condition used to identify material items. |
| `partials/page-data.php:79` | `equipment_condition($itemAlias, $categoryAlias)` | Admin dashboard, equipment page, student browse | Builds the SQL condition used to identify equipment items. |
| `partials/page-data.php:84` | `unit_price_from_description($description)` | `pages/materials.php` | Reads the unit price stored inside an item description. |
| `partials/page-data.php:93` | `plain_description($description)` | Equipment, materials, student browse | Removes internal metadata lines before showing descriptions. |
| `partials/page-data.php:103` | `meta_value($description, $label)` | `pages/equipment.php` | Extracts metadata such as item code or condition from description text. |
| `partials/page-data.php:112` | `badge($status)` | Requests, dashboards, transactions | Renders a colored status badge. |
| `pages/admin-settings.php:313` | `setUserForRoleEdit(username, fullName, currentRole)` | `pages/admin-settings.php:229` | Fills the role-edit form when an admin chooses a user. |
| `pages/students.php:184` | `syncMasterlistFields(form)` | `pages/students.php:200`, `213`, `221`, `224` | Switches student/faculty form fields and required inputs. |
| `pages/users.php:215` | `setAuthorizationData(userId, username, fullName, currentAuth)` | User action buttons/form flow | Loads selected user information into the authorization/role modal. |
| `process/admin_settings.php:7` | `csv_headers($headers)` | `process/admin_settings.php:192` | Normalizes uploaded CSV header names. |
| `process/admin_settings.php:14` | `csv_value($row, $headers, $keys)` | CSV import helpers | Reads one CSV field using accepted alternate column names. |
| `process/admin_settings.php:26` | `csv_has_any($headers, $keys)` | `process/admin_settings.php:70` | Checks whether a CSV has one required header from a group. |
| `process/admin_settings.php:37` | `csv_validate_headers($dataset, $headers)` | `process/admin_settings.php:193` | Validates required CSV columns per import type. |
| `process/admin_settings.php:76` | `csv_row_is_blank($row)` | `process/admin_settings.php:212` | Skips empty CSV rows. |
| `process/admin_settings.php:87` | `save_masterlist_csv_row(...)` | `process/admin_settings.php:225` | Imports one student/faculty/masterlist CSV row. |
| `process/admin_settings.php:136` | `save_item_csv_row(...)` | `process/admin_settings.php:227` | Imports one equipment/material CSV row. |
| `process/admin_settings.php:179` | `import_csv($pdo, $dataset, $filePath, $mode)` | `process/admin_settings.php:329` | Reads the uploaded CSV and records total/success/failed counts. |
| `process/admin_settings.php:282` | `update_settings_user_role(...)` | `process/admin_settings.php:354` | Changes a user role from the admin settings page. |
| `process/users.php:7` | `update_user_role(...)` | `process/users.php:82` | Changes a user role from the users page. |
| `process/_helpers.php:8` | `clean($value)` | Page filters and process handlers | Trims and safely converts values to strings. |
| `process/_helpers.php:13` | `compact_spaces($value)` | Form handlers and helpers | Normalizes repeated whitespace to single spaces. |
| `process/_helpers.php:18` | `post_value($key, $default)` | All process handlers | Reads a cleaned value from `$_POST`. |
| `process/_helpers.php:23` | `db_exec($pdo, $sql, $params)` | All process handlers | Prepares and executes SQL with parameters. |
| `process/_helpers.php:30` | `table_exists(...)` | `ensure_system_schema()` | Checks if a database table exists. |
| `process/_helpers.php:43` | `column_exists(...)` | `ensure_system_schema()` | Checks if a database column exists. |
| `process/_helpers.php:56` | `column_is_nullable(...)` | `ensure_system_schema()` | Checks whether a column allows `NULL`. |
| `process/_helpers.php:70` | `index_exists(...)` | `ensure_system_schema()` | Checks if a database index already exists. |
| `process/_helpers.php:83` | `ensure_system_schema($pdo)` | `process/_helpers.php:219`, `957` | Creates/updates system columns and audit table once per request. |
| `process/_helpers.php:145` | `request_wants_json()` | `respond_error()`, `respond_success()` | Detects JSON/AJAX requests. |
| `process/_helpers.php:153` | `redirect_to($path, $params)` | Login, guards, response helpers | Redirects to another page with optional query parameters. |
| `process/_helpers.php:163` | `redirect_back($fallback, $params)` | Password change and error handling | Redirects to the referrer or fallback page. |
| `process/_helpers.php:168` | `respond_error(...)` | All process handlers | Sends a JSON error or redirects with an error code. |
| `process/_helpers.php:192` | `respond_success(...)` | All process handlers | Sends a JSON success or redirects with a success code. |
| `process/_helpers.php:205` | `rollback_if_active($pdo)` | Error handlers | Rolls back an open database transaction. |
| `process/_helpers.php:212` | `log_internal_error($context, $error)` | Error handlers | Writes internal errors to PHP logs. |
| `process/_helpers.php:217` | `log_audit(...)` | Login and data changes | Records important actions in `audit_log`. |
| `process/_helpers.php:236` | `require_post($fallback)` | All process handlers | Blocks non-POST form requests. |
| `process/_helpers.php:243` | `destroy_current_session()` | `require_login()` | Clears an invalid session. |
| `process/_helpers.php:263` | `require_login()` | Page/process guards | Requires a logged-in active user and handles forced password changes. |
| `process/_helpers.php:299` | `valid_role($role)` | `require_login()` | Allows only `student`, `faculty`, or `admin`. |
| `process/_helpers.php:304` | `is_admin_role($role)` | `require_admin()` | Allows admin/faculty access to admin-side pages. |
| `process/_helpers.php:309` | `can_manage_borrow_workflow($role)` | Sidebar/page helpers and guards | Allows only admins to approve/reject/return borrowing records. |
| `process/_helpers.php:314` | `can_manage_user_roles($role)` | Sidebar/page helpers and guards | Allows only admins to manage roles. |
| `process/_helpers.php:319` | `require_admin()` | Admin pages and handlers | Redirects non-admin-side users away from admin pages. |
| `process/_helpers.php:328` | `require_borrow_workflow_manager()` | Requests/transactions pages and handlers | Restricts workflow actions to admins. |
| `process/_helpers.php:337` | `require_user_role_manager()` | Users page and handler | Restricts role management to admins. |
| `process/_helpers.php:346` | `require_borrower()` | Student pages and request handler | Allows only student borrowers. |
| `process/_helpers.php:355` | `db_role($role)` | User creation and role updates | Converts UI labels into database role values. |
| `process/_helpers.php:370` | `home_for_role($role)` | Login and guard redirects | Chooses student dashboard or admin dashboard. |
| `process/_helpers.php:375` | `split_full_name($fullName)` | User and masterlist handlers | Splits one full-name field into first and last names. |
| `process/_helpers.php:389` | `normalized_year($value)` | Student/masterlist handlers | Accepts only four-digit year values. |
| `process/_helpers.php:395` | `require_non_negative_int(...)` | Inventory and CSV handlers | Validates whole numbers greater than or equal to zero. |
| `process/_helpers.php:406` | `require_positive_int(...)` | IDs, quantities, student requests | Validates whole numbers greater than zero. |
| `process/_helpers.php:417` | `normalized_date_or_today($value)` | CSV/material/student request handlers | Validates `YYYY-MM-DD`, defaulting blank dates to today. |
| `process/_helpers.php:433` | `get_or_create_course(...)` | Student import and student save | Reuses or creates a course record. |
| `process/_helpers.php:453` | `get_or_create_category(...)` | CSV import and category save | Reuses or creates a category record. |
| `process/_helpers.php:492` | `require_existing_category_id(...)` | Equipment/material saves | Requires a valid category by ID or name. |
| `process/_helpers.php:519` | `get_or_create_unit(...)` | CSV import and unit save | Reuses or creates a unit record. |
| `process/_helpers.php:555` | `require_existing_unit_id(...)` | Equipment/material saves | Requires a valid unit by ID or name. |
| `process/_helpers.php:582` | `get_or_create_position(...)` | Faculty import and official user creation | Reuses or creates a faculty/admin position. |
| `process/_helpers.php:605` | `require_existing_course_code(...)` | Masterlist save | Requires a valid existing course. |
| `process/_helpers.php:633` | `require_existing_position_code(...)` | Faculty masterlist save | Requires a valid existing position. |
| `process/_helpers.php:661` | `next_student_id(...)` | Manual student user creation | Generates an unused student ID. |
| `process/_helpers.php:679` | `save_master_record(...)` | Student account helpers | Inserts or updates a student masterlist row. |
| `process/_helpers.php:702` | `save_official_record(...)` | Official account helpers | Inserts or updates a faculty/official masterlist row. |
| `process/_helpers.php:725` | `user_display_name_by_id(...)` | `process/login.php:25` | Finds the display name stored in session after login. |
| `process/_helpers.php:743` | `default_account_password(...)` | Account creation helpers | Builds the first/default password from identifier plus last name. |
| `process/_helpers.php:748` | `ensure_user_for_student(...)` | CSV, students, masterlist | Creates or syncs a student user account. |
| `process/_helpers.php:803` | `ensure_user_for_official(...)` | CSV, masterlist, manual official users | Creates or syncs a faculty/admin account. |
| `process/_helpers.php:861` | `create_manual_user(...)` | `process/users.php:108` | Creates a user from the Users page. |
| `process/_helpers.php:904` | `inventory_stock_status(...)` | Inventory saves, approvals, returns | Converts available quantity into stock status. |
| `process/_helpers.php:916` | `assert_item_not_duplicate(...)` | Inventory saves/imports | Prevents duplicate item names inside the same category. |
| `process/_helpers.php:932` | `item_dependency_count(...)` | Equipment/material deletes | Blocks deleting items that already have request/transaction history. |
| `process/_helpers.php:940` | `password_matches(...)` | Login and password change | Accepts hashed passwords and upgrades legacy plain passwords. |
| `process/_helpers.php:945` | `days_between(...)` | `process/student_browse.php:18` | Calculates borrow duration from start and due dates. |

## Variable Labels

### Shared Variables

| Variable | Files/lines | How used |
| --- | --- | --- |
| `$pdo` | `db.php:13`; many process/helper files | Shared PDO database connection. |
| `$stmt` | Process/helper files | Prepared statement returned by `db_exec()` or `$pdo->prepare()`. |
| `$error` | Process/helper catch blocks | Caught exception/throwable used for rollback, logging, and error codes. |
| `$code` | Process catch blocks | User-facing error/success code sent through redirects or JSON. |
| `$action` | Most process files | Selected form operation, commonly `save`, `delete`, `approve`, `reject`, `return`, or `deactivate`. |
| `$currentUser` | `partials/page-data.php:132`; page headers | Current logged-in user record. |
| `$currentName` | `partials/page-data.php:133`; page headers | Display name for the current user. |
| `$currentInitials` | `partials/page-data.php:134`; page headers | Initials shown in the profile circle. |
| `$canManageBorrowWorkflow` | `partials/page-data.php:135`; admin sidebars/pages | Permission flag for request approval and return actions. |
| `$canManageUserRoles` | `partials/page-data.php:136`; admin pages | Permission flag for user/role management UI. |
| `$pendingCount` | `partials/page-data.php:137`; sidebars | Pending borrow request badge count. |

### Database Connection Variables

| File | Variables | How used |
| --- | --- | --- |
| `db.php` | `$dbHost:4`, `$dbName:5`, `$dbUser:6`, `$dbPass:7`, `$dbCharset:8`, `$dsn:10`, `$pdo:13`, `$e:18` | Configures MySQL connection, creates the PDO object, and catches connection errors. |

### Page Variables

| File | Variables | How used |
| --- | --- | --- |
| `pages/admin-dashboard.php` | `$equipmentWhere:5`, `$totalEquipment:6`, `$pendingRequests:7`, `$activeBorrowings:8`, `$returnedItems:9`, `$totalStudents:10`, `$systemUsers:11`, `$recentRequests:12`, `$recentTransactions:21`, `$quickActivity:30`, `$request`, `$transaction`, `$activity`, `$a`, `$b` | Builds dashboard metric cards, recent request/transaction tables, and sorted quick activity. |
| `pages/admin-settings.php` | `$categoryRows:4`, `$unitRows:5`, `$positionRows:6`, `$settingsUsers:7`, `$settingsOfficials:17`, `$settingsBorrowers:20`, `$importSummary:23`, `$category`, `$unit`, `$position`, `$official`, `$borrower`, `$fullName`, `$roleLabel`, `$user` | Loads settings tables, CSV import summary, category/unit/position lists, and user role controls. |
| `pages/change-password.php` | `$currentInitials`, `$currentName`, `$currentUser`, `$canManageBorrowWorkflow`, `$canManageUserRoles`, `$pendingCount` | Uses shared header/sidebar permission and profile variables. |
| `pages/courses.php` | `$courseRows:4`, `$course`, plus shared sidebar/profile variables | Displays and edits course records. |
| `pages/equipment.php` | `$categoryFilter:5`, `$statusFilter:6`, `$sort:7`, `$where:8`, `$params:9`, `$orderBy:24`, `$equipmentRows:31`, `$equipmentCategories:40`, `$categoryRows:47`, `$unitRows:48`, `$item`, `$itemCode`, `$condition`, `$displayStatus`, `$description`, `$category`, `$statusOption`, `$unit` | Filters, sorts, displays, creates, and edits equipment records. |
| `pages/login.php` | `$loginError:2`, `$loginMessage:3` | Chooses the login error message shown to the user. |
| `pages/materials.php` | `$categoryFilter:5`, `$sort:6`, `$where:7`, `$params:8`, `$orderBy:15`, `$materialRows:22`, `$materialCategories:38`, `$categoryRows:45`, `$unitRows:46`, `$totalMaterials:47`, `$totalQuantity:48`, `$totalValue:51`, `$totalCategories:55`, `$out:62`, `$row`, `$unitPrice`, `$lineValue`, `$description`, `$item`, `$valueA`, `$valueB`, `$a`, `$b` | Filters materials, computes totals/value, exports CSV, and renders material cards/forms. |
| `pages/requests.php` | `$requestRows:5`, `$request`, `$statusKey`, plus shared profile variables | Displays admin borrow requests and status/action rows. |
| `pages/student-browse.php` | `$categoryFilter:5`, `$where:6`, `$params:7`, `$equipmentRows:14`, `$equipmentCategories:23`, `$category`, `$item` | Lets students browse available equipment and open the borrow request modal. |
| `pages/student-dashboard.php` | `$studentId:5`, `$activeRequests:6`, `$approvedRequests:7`, `$borrowedItems:8`, `$returnedItems:9`, `$recentRequests:10`, `$request`, `$displayStatus` | Builds student dashboard counters and recent request history. |
| `pages/student-requests.php` | `$myRequestRows:5`, `$request`, `$displayStatus`, `$statusKey` | Lists the logged-in student's borrow requests. |
| `pages/students.php` | `$studentRows:5`, `$facultyRows:17`, `$masterRows:24`, `$courseRows:25`, `$positionRows:26`, `$person`, `$fullName`, `$course`, `$position` | Combines student/faculty masterlists and feeds add/edit dropdowns. |
| `pages/transactions.php` | `$transactionRows:5`, `$transaction`, `$isReturned`, `$isOverdue`, `$statusKey`, `$statusText` | Lists borrowing transactions, overdue state, and return buttons. |
| `pages/users.php` | `$userRows:5`, `$borrowerRows:15`, `$officialRows:24`, `$user`, `$official`, `$borrower`, `$roleLabel`, `$fullName` | Displays system users, authorization users, and user creation/role forms. |
| `partials/head.php` | `$pageTitle`, `$assetsBase` | Sets page title and stylesheet path. |
| `partials/sidebar-admin.php` | `$current`, `$canManageBorrowWorkflow`, `$canManageUserRoles`, `$pendingCount`, `$_SESSION['user']` | Highlights/limits sidebar links and displays logged-in user text. |

### Process Handler Variables

| File | Variables | How used |
| --- | --- | --- |
| `process/admin_settings.php` | `$headers`, `$row`, `$keys`, `$dataset`, `$requirements`, `$group`, `$userType`, `$firstName`, `$lastName`, `$fullName`, `$officialId`, `$department`, `$positionCode`, `$studentId`, `$courseCode`, `$itemName`, `$categoryId`, `$unitId`, `$quantity`, `$description`, `$dateAdded`, `$unitPrice`, `$itemCode`, `$condition`, `$stockStatus`, `$itemId`, `$handle`, `$rawHeaders`, `$strict`, `$summary`, `$rowNumber`, `$rowError`, `$mode`, `$filePath`, `$userId`, `$username`, `$newRole`, `$role`, `$user`, `$originalName` | Validates CSV imports, saves imported rows, tracks import results, and updates user roles from settings. |
| `process/categories.php` | `$action`, `$categoryId`, `$categoryName`, `$category`, `$itemCount`, `$current`, `$duplicate`, `$targetId`, `$code` | Creates, updates, merges, or deletes category records. |
| `process/change_password.php` | `$currentPassword`, `$newPassword`, `$confirmPassword`, `$fallback`, `$stmt`, `$user`, `$error` | Validates current password and saves a new password. |
| `process/courses.php` | `$action`, `$courseCode`, `$courseName`, `$studentCount`, `$code` | Creates, updates, or deletes course records. |
| `process/equipment.php` | `$itemId`, `$action`, `$itemCode`, `$itemName`, `$condition`, `$postedStatus`, `$status`, `$description`, `$item`, `$quantity`, `$categoryId`, `$unitId`, `$notes`, `$currentItem`, `$borrowedQuantity`, `$availableQuantity`, `$stockStatus`, `$code` | Creates, updates, or deletes equipment and recalculates available stock. |
| `process/login.php` | `$username`, `$password`, `$stmt`, `$user`, `$passwordPage` | Authenticates a user, sets session variables, upgrades plain passwords, and redirects by role. |
| `process/logout.php` | `$_SESSION` | Clears the active login session. |
| `process/masterlist.php` | `$action`, `$userType`, `$idNumber`, `$firstName`, `$lastName`, `$fullName`, `$departmentOrCourse`, `$courseCodeInput`, `$positionCodeInput`, `$yearLevel`, `$dependents`, `$studentId`, `$positionCode`, `$userId`, `$courseCode`, `$code` | Saves or deletes student/faculty masterlist records and synced users. |
| `process/materials.php` | `$itemId`, `$action`, `$materialName`, `$description`, `$unitPrice`, `$item`, `$quantity`, `$dateAdded`, `$categoryId`, `$unitId`, `$notes`, `$currentItem`, `$borrowedQuantity`, `$availableQuantity`, `$stockStatus`, `$code` | Creates, updates, or deletes material records and value metadata. |
| `process/positions.php` | `$action`, `$positionCode`, `$positionName`, `$stmt`, `$position`, `$officialCount`, `$code` | Creates, updates, or deletes faculty/admin positions. |
| `process/requests.php` | `$requestId`, `$action`, `$remarks`, `$stmt`, `$request`, `$expectedReturn`, `$remainingQuantity`, `$stockStatus`, `$knownCodes`, `$code` | Approves or rejects borrow requests, creates transactions, and deducts stock. |
| `process/students.php` | `$action`, `$studentId`, `$fullName`, `$courseCode`, `$yearLevel`, `$dependents`, `$firstName`, `$lastName`, `$userId`, `$error` | Legacy/simple student save/delete handler. |
| `process/student_browse.php` | `$studentId`, `$itemId`, `$purpose`, `$quantity`, `$borrowDate`, `$postedDays`, `$dueDate`, `$days`, `$stmt`, `$item`, `$requestId`, `$code` | Creates a student borrow request after validating stock and dates. |
| `process/transactions.php` | `$transactionId`, `$action`, `$stmt`, `$transaction`, `$newAvailable`, `$stockStatus`, `$code` | Marks a transaction returned and restores item availability. |
| `process/units.php` | `$action`, `$unitId`, `$unitName`, `$unit`, `$itemCount`, `$current`, `$duplicate`, `$targetId`, `$code` | Creates, updates, merges, or deletes unit records. |
| `process/users.php` | `$userId`, `$username`, `$newRole`, `$role`, `$stmt`, `$user`, `$action`, `$editUsername`, `$fullName`, `$password`, `$isActive`, `$firstName`, `$lastName`, `$error` | Creates users, deletes/deactivates users, and updates roles. |
| `process/return_item.php` | none locally | Includes `process/transactions.php` to reuse the return handler. |

### Helper File Local Variables

| File | Variables | How used |
| --- | --- | --- |
| `process/_helpers.php` | `$value`, `$key`, `$default`, `$sql`, `$params`, `$table`, `$column`, `$index`, `$checked`, `$accept`, `$xhr`, `$path`, `$fallback`, `$target`, `$message`, `$status`, `$extra`, `$payload`, `$context`, `$actionType`, `$tableName`, `$recordId`, `$details`, `$actorUserId`, `$role`, `$currentScript`, `$passwordPages`, `$passwordPage`, `$parts`, `$firstName`, `$lastName`, `$number`, `$date`, `$courseCode`, `$courseName`, `$name`, `$row`, `$categoryId`, `$categoryName`, `$unitId`, `$unitName`, `$positionCode`, `$positionName`, `$preferred`, `$studentId`, `$officialId`, `$identifier`, `$username`, `$existing`, `$isActive`, `$password`, `$itemName`, `$itemId`, `$availableQuantity`, `$threshold`, `$requests`, `$transactions`, `$plain`, `$stored`, `$startDate`, `$endDate`, `$start`, `$end`, `$days`, `$e` | Local helper inputs and temporary values for validation, redirects, authorization, account creation, inventory status, duplicate checks, password handling, and date math. |

### JavaScript Variables

| File | Variables | How used |
| --- | --- | --- |
| `js/shared.js` | `current:32`, `a:33`, `href:34`, `btn:41/49`, `aside:42`, `id:51`, `m:52/56/65`, `e:57/63/107`, `group:72`, `tabs:73`, `tab:74`, `t:76`, `filter:78`, `targetSel:79`, `rows:80`, `row:81/96`, `s:82`, `input:92`, `q:94`, `sel:95`, `text:97`, `el:19/106`, `msg:108` | DOM references and event values for page fade-in, nav highlighting, modals, tabs, search, and confirmation prompts. |
| `pages/admin-settings.php` | `username`, `fullName`, `currentRole` in `setUserForRoleEdit()` | Fills modal fields for role updates. |
| `pages/students.php` | `form`, `isFaculty` in `syncMasterlistFields()` | Toggles form labels/required fields for student versus faculty records. |
| `pages/users.php` | `role`, `userId`, `username`, `fullName`, `currentAuth` | Drives role-specific UI and authorization modal values. |
| `pages/equipment.php`, `pages/materials.php`, `pages/courses.php`, `pages/student-browse.php` | `form` | Resets or fills modal form fields for add/edit/borrow actions. |

