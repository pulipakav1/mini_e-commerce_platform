# Database Schema Fixes Applied

## Summary of Changes

### 1. **Replaced `admins` table with `employees` table**

All admin/employee operations now use the `employees` table instead of the non-existent `admins` table.

**Files Updated:**
- `admin/admin_login.php` - Uses `employees` table, `employee_userid`, `employee_type`
- `admin/hr.php` - Queries `employees` table
- `admin/add_employee.php` - Inserts into `employees` table
- `admin/edit_employee.php` - Updates `employees` table
- `admin/view_employee.php` - Views from `employees` table
- `admin/delete_employee.php` - Deletes from `employees` table
- `admin/reports.php` - Counts from `employees` table
- `admin/dab.php` - Creates employees in `employees` table

**Column Mapping:**
- `admins.id` → `employees.employee_id`
- `admins.admin_userid` → `employees.employee_userid`
- `admins.role` → `employees.employee_type`
- `admins.admin_password` → `employees.employee_password`

### 2. **Removed Addresses, Countries, States, Cities Tables**

Since these tables don't exist, all related code has been removed or updated.

**Files Deleted:**
- `addresses.php` - Deleted (table doesn't exist)
- `add_address.php` - Deleted (table doesn't exist)
- `get_location.php` - Deleted (location tables don't exist)

**Files Updated:**
- `profile.php` - Changed link to `edit_addresses.php` (edits addresses directly in users table)
- `edit_addresses.php` - NEW FILE: Edits shipping_address, billing_address, phone_number directly in `users` table

### 3. **Address Storage**

Addresses are now stored directly in the `users` table:
- `shipping_address` (TEXT)
- `billing_address` (TEXT)
- `phone_number` (VARCHAR)

No separate addresses table is needed.

### 4. **Missing Fields That Need to Be Added**

Run the SQL commands in `ADD_MISSING_FIELDS.sql` to add required authentication fields:

**To `users` table:**
- `email VARCHAR(255) UNIQUE` - For login and password reset
- `password VARCHAR(255)` - For password hashing
- `upload_profile VARCHAR(255)` - Optional, for profile pictures

**To `employees` table:**
- `employee_userid VARCHAR(150) NOT NULL UNIQUE` - For employee login
- `email VARCHAR(255)` - For employee email
- `employee_password VARCHAR(255)` - For employee password hashing

## Next Steps

1. **Run `ADD_MISSING_FIELDS.sql`** in phpMyAdmin to add the missing columns to your database tables.

2. **Verify column names match:**
   - Users table uses: `user_id`, `user_name`, `name` (not `id`, `username`, `fullname`)
   - Employees table uses: `employee_id`, `employee_userid`, `employee_type` (not `id`, `admin_userid`, `role`)

3. **Test employee login:**
   - Use `admin/admin_login.php`
   - Credentials are stored in `employees` table with `employee_userid` and `employee_password`

4. **Test user signup/login:**
   - Users are created in `users` table with `user_name`, `email`, `password`
   - Login uses `user_name` and `password`

## Current Database Structure

### Tables That Exist:
- ✅ `users` - Customer accounts
- ✅ `employees` - Employee accounts (replaces admins)
- ✅ `products` - Product catalog
- ✅ `category` - Product categories
- ✅ `orders` - Customer orders
- ✅ `order_items` - Items in each order
- ✅ `receipts` - Receipt records
- ✅ `payment` - Payment records
- ✅ `inventory` - Product inventory
- ✅ `images` - Product images
- ✅ `cart` - Shopping cart

### Tables That DON'T Exist (and are no longer used):
- ❌ `admins` - Replaced by `employees`
- ❌ `addresses` - Addresses stored in `users` table
- ❌ `countries` - Not used
- ❌ `states` - Not used
- ❌ `cities` - Not used

## Code Consistency

All code now:
- Uses `employees` table for admin/employee operations
- Uses `user_id`, `user_name`, `name` for users table
- Uses `employee_id`, `employee_userid`, `employee_type` for employees table
- Stores addresses directly in `users` table
- No references to non-existent tables

