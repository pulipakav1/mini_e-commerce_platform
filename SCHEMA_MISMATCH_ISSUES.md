# CRITICAL DATABASE SCHEMA MISMATCH

## Schema from tables.sql vs PHP Code Usage

### MAJOR ISSUES FOUND:

#### 1. **USERS TABLE MISMATCH**

**Schema (tables.sql):**
- Primary Key: `user_id` 
- Fields: `user_name`, `name`, `role`, `phone_number`, `shipping_address`, `billing_address`
- NO `email` field
- NO `password` field  
- NO `username` field (has `user_name`)
- NO `fullname` field (has `name`)
- NO `upload_profile` field

**PHP Code Currently Uses:**
- `id` (should be `user_id`)
- `email` (DOES NOT EXIST in schema)
- `password` (DOES NOT EXIST in schema)
- `username` (should be `user_name`)
- `fullname` (should be `name`)
- `upload_profile` (DOES NOT EXIST)

**FIX NEEDED:** 
- Schema appears incomplete - missing auth fields OR
- PHP code needs major updates to match schema

#### 2. **ADMINS TABLE - DOES NOT EXIST**

**Schema (tables.sql):**
- NO `admins` table exists!
- Has `employees` table instead with: `employee_id`, `employee_type`, `salary`, `hire_date`
- NO authentication fields in employees table

**PHP Code Currently Uses:**
- References `admins` table everywhere
- Uses fields: `id`, `admin_userid`, `role`, `email`, `salary`, `admin_password`
- None of these match employees table!

**FIX NEEDED:**
- Either create `admins` table OR
- Completely rewrite admin code to use `employees` table

#### 3. **PRODUCTS TABLE - IMAGE FIELD MISSING**

**Schema (tables.sql):**
- NO `product_image` field
- Uses separate `images` table with `file_path`

**PHP Code:**
- Uses `product_image` field directly in products table

**FIX NEEDED:**
- Update all product code to use `images` table instead

#### 4. **ORDERS TABLE COLUMN NAMES**

**Schema (tables.sql):**
- Primary Key: `order_id`
- Has `shipping_address`, `billing_address` directly (not separate address table)

**PHP Code:**
- Uses `id` (should be `order_id`)
- May expect address_id reference

#### 5. **CATEGORY TABLE**
- Schema and code match ✓

## RECOMMENDED FIXES:

### Option 1: Update Schema (if you can modify database)
Add missing fields to match PHP code:
- Add `email`, `password` to users table
- Create `admins` table with proper auth fields
- Add `product_image` to products table OR keep using images table

### Option 2: Update PHP Code (to match current schema)
- Change all `id` to `user_id` for users
- Change `username` to `user_name`  
- Change `fullname` to `name`
- Remove email/password authentication (if schema has none)
- Rewrite admin system to use `employees` table
- Update product image handling to use `images` table

