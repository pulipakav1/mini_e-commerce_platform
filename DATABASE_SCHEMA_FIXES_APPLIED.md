# Database Schema Fixes Applied

## FIXES COMPLETED

### 1. **USERS TABLE - Column Names Updated**

**Changed in PHP:**
- `id` → `user_id` (primary key)
- `username` → `user_name` 
- `fullname` → `name`
- All queries now use correct column names

**Files Updated:**
- login.php
- signup.php
- home.php
- profile.php
- change_password.php
- forgot_password.php
- reset_password.php
- home_living.php
- cups_bottles.php
- style_accessories.php
- tulip_collection.php
- indoor_plants.php

**NOTE:** Schema shows no `email` or `password` fields. Code includes them with checks. If your actual database doesn't have these, you need to:
- Add `email VARCHAR(255) UNIQUE` to users table
- Add `password VARCHAR(255)` to users table

### 2. **PRODUCTS TABLE - Images Handling Updated**

**Schema:** Products table has NO `product_image` field. Images stored in separate `images` table.

**Changes Made:**
- `admin/products.php` - Now inserts into `images` table after creating product
- `admin/edit_product.php` - Fetches/updates images from `images` table
- `admin/orders.php` - Fetches images from `images` table for display
- `admin/view_products.php` - Fetches images from `images` table

**Image Table Structure:**
- `image_id` (primary key)
- `product_id` (foreign key)
- `file_path`
- `alt_text`

### 3. **ORDERS TABLE - Corrected Column Names**

**Fixed:**
- `my_orders.php` - Now uses `order_id` instead of `id`
- Updated to join with `order_items` table to get product details
- Orders table has: `order_id`, `user_id`, `order_date`, `shipping_address`, `billing_address`, `total_amount`

**Files Updated:**
- my_orders.php (now properly joins order_items)

### 4. **PAYMENT - Removed Credit Card Storage**

**CRITICAL FIX:**
- Removed ALL credit card input fields
- Removed card_number, expiry, cvv storage
- Only allows "Cash on Delivery"
- Updated to match `payment` table schema (which requires order_id)

**File Updated:**
- payment.php

### 5. **CATEGORY TABLE**

**Status:** ✅ Already correct
- Uses `category_id` and `category_name` correctly

### 6. **PRODUCTS TABLE**

**Status:** ✅ Column names correct
- `product_id`, `product_name`, `product_description`, `category_id`, `cost`, `quantity` all match schema

## REMAINING SCHEMA MISMATCHES

### 1. **ADMINS vs EMPLOYEES TABLE**

**Issue:** 
- Schema shows `employees` table exists
- Code uses `admins` table everywhere
- `employees` table has: `employee_id`, `employee_type`, `salary`, `hire_date`
- Code expects: `id`, `admin_userid`, `role`, `email`, `salary`, `admin_password`

**Action Needed:**
- Either create `admins` table OR
- Rewrite all admin code to use `employees` table
- Add authentication fields to whichever table you use

**Files Affected:**
- admin/admin_login.php
- admin/hr.php
- admin/add_employee.php
- admin/edit_employee.php
- admin/delete_employee.php
- admin/view_employee.php
- admin/reports.php
- admin/dashboard.php

### 2. **ADDRESSES TABLE**

**Issue:**
- Code uses `addresses` table with `id`, `user_id`, `country_id`, `state_id`, `city_id`, `zip`, `address_line`
- Schema `tables.sql` doesn't show `addresses` table, but `orders` has shipping_address/billing_address directly

**Action Needed:**
- Verify if `addresses` table exists
- If not, either create it OR use addresses stored directly in users/orders tables

### 3. **COUNTRIES, STATES, CITIES TABLES**

**Issue:**
- Code references these tables
- Not in `tables.sql` schema provided

**Action Needed:**
- Verify these tables exist
- Create if missing OR update code if they don't exist

### 4. **USERS TABLE - Missing Auth Fields**

**Schema shows:**
- user_id, user_name, name, role, phone_number, shipping_address, billing_address

**Code needs:**
- email (for login/reset password)
- password (for authentication)
- upload_profile (for profile pictures)

**Action Needed:**
- Add these fields to users table:
  ```sql
  ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE;
  ALTER TABLE users ADD COLUMN password VARCHAR(255);
  ALTER TABLE users ADD COLUMN upload_profile VARCHAR(255);
  ```

## DATABASE SCHEMA SUMMARY

### Tables from schema.sql:
1. ✅ **users** - user_id, user_name, name, role, phone_number, shipping_address, billing_address
2. ✅ **products** - product_id, product_name, product_description, category_id, cost, quantity
3. ✅ **category** - category_id, category_name, category_description
4. ✅ **orders** - order_id, user_id, order_date, shipping_address, billing_address, total_amount
5. ✅ **order_items** - order_item_id, order_id, product_id, quantity, unit_price
6. ✅ **receipts** - receipt_id, order_id, receipt_number, receipt_date
7. ✅ **payment** - payment_id, order_id, payment_method, total_amount, payment_date
8. ✅ **images** - image_id, product_id, file_path, alt_text
9. ✅ **cart** - cart_id, product_id, user_id, quantity
10. ✅ **employees** - employee_id, employee_type, salary, hire_date
11. ✅ **inventory** - inventory_id, product_id, quantity, last_updated

### Tables NOT in schema but used in code:
- ❓ **admins** - Used everywhere but not in schema (should use employees?)
- ❓ **addresses** - Used but not in schema
- ❓ **countries** - Used but not in schema
- ❓ **states** - Used but not in schema
- ❓ **cities** - Used but not in schema
- ❓ **payment_methods** - Used but schema has `payment` table instead

## RECOMMENDATIONS

1. **Verify Actual Database Structure**
   - Run `SHOW TABLES;` to see what tables actually exist
   - Run `DESCRIBE tablename;` for each table to see actual columns

2. **Fix Authentication**
   - Ensure users table has email and password fields
   - Ensure admins/employees table has authentication fields

3. **Standardize Image Handling**
   - All product images now correctly use `images` table
   - No more direct `product_image` field references

4. **Fix Payment System**
   - Removed credit card storage (as required)
   - Payment will be recorded when order is placed (requires order_id)

5. **Address System**
   - Verify if addresses table exists
   - If not, users already have shipping_address/billing_address in users table

## NEXT STEPS

1. Check actual database structure
2. Create missing tables or update code
3. Add missing columns (email, password) to users table
4. Decide on admins vs employees table
5. Test all functionality

