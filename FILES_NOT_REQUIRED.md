# Files NOT Required According to Project Description

Based on the project requirements analysis, these files are **NOT explicitly required** and can be removed:

## Files to Remove:

### 1. **saved_orders.php** ❌
- **Reason**: This is NOT order history - it's about "saved products" (like wishlist)
- **Clarification**: Order history IS required and is handled by `my_orders.php` (which queries `orders` table)
- **Problem**: `saved_orders.php` references tables that don't exist (`saved_products`, `flowers`) - schema has `products` and `orders` tables instead
- **Similar to**: wishlist.php (already removed) - it's a saved/favorites feature, not order history

### 2. **change_password.php** ❌
- **Reason**: Password management not explicitly required
- **Note**: Security requirement is "passwords should not be saved in plaintext" (already implemented with password_hash), but password change feature is not required

### 3. **forgot_password.php** ❌
- **Reason**: Password recovery not explicitly required
- **Note**: Only login requirement is "first page should be login page", no mention of password recovery

### 4. **reset_password.php** ❌
- **Reason**: Part of password recovery (not required)
- **Status**: Works with forgot_password.php

### 5. **payment.php** ❌
- **Reason**: Payment is handled in `checkout.php`
- **Note**: The project requirement states payment should be part of transaction completion, which is already handled in checkout.php. This separate page is redundant.
- **Status**: Currently only shows "Cash on Delivery" message, functionality already in checkout

### 6. **edit_addresses.php** ❌
- **Reason**: Addresses are entered during signup (required), but editing is not required
- **Note**: Users provide billing_address and shipping_address during signup, which is sufficient

### 7. **upload_profile.php** ❌
- **Reason**: Profile picture upload not required
- **Note**: The schema doesn't even have `upload_profile` field in users table
- **Status**: File exists but functionality is commented out

---

## Files That Are OPTIONAL (Can Keep for Better UX):

### 1. **profile.php** ⚠️ OPTIONAL
- **Reason**: Not explicitly required, but useful for users to view their information
- **Recommendation**: Can keep for better user experience
- **Note**: Links to other non-required pages (edit_addresses.php, payment.php, change_password.php) would need to be removed

### 2. **admin/reports.php** ⚠️ OPTIONAL
- **Reason**: Not explicitly required, but useful for business manager analytics
- **Recommendation**: Can keep as it shows data views for business manager role

---

## Files That ARE REQUIRED (Do NOT Remove):

### User/Customer Pages:
✅ `login.php` - Required (first page must be login)
✅ `signup.php` - Required (user registration)
✅ `home.php` - Required (public/shopper view)
✅ `cart.php` - Required (shopping cart)
✅ `add_to_cart.php` - Required (add to cart)
✅ `checkout.php` - Required (complete transaction)
✅ `order_confirmation.php` - Required (order completion)
✅ `my_orders.php` - Required (order history by date/time)
✅ `education.php` - Required (flower education section)
✅ Category pages (`home_living.php`, `cups_bottles.php`, etc.) - Required (e-commerce shop)

### Admin/Employee Pages:
✅ `admin/admin_login.php` - Required (employee access)
✅ `admin/dashboard.php` - Required (admin view)
✅ `admin/products.php` - Required (CRUD - insert)
✅ `admin/view_products.php` - Required (CRUD - view/search/delete)
✅ `admin/edit_product.php` - Required (CRUD - modify)
✅ `admin/orders.php` - Required (manage orders)
✅ `admin/hr.php` - Required (HR section)
✅ `admin/add_employee.php` - Required (HR - add)
✅ `admin/edit_employee.php` - Required (HR - modify)
✅ `admin/view_employee.php` - Required (HR - view)
✅ `admin/delete_employee.php` - Required (HR - delete)

### Core Files:
✅ `db.php` - Required (database connection)
✅ `logout.php` - Required (user logout)
✅ `admin/logout.php` - Required (admin logout)

---

## Summary:

**Total files to remove: 7**
1. saved_orders.php
2. change_password.php
3. forgot_password.php
4. reset_password.php
5. payment.php
6. edit_addresses.php
7. upload_profile.php

**Files to optionally keep (but remove links to non-required pages):**
- profile.php (remove links to edit_addresses.php, payment.php, change_password.php)

**Action Required:**
1. Remove the 7 files listed above
2. Update `profile.php` to remove links to non-required pages
3. Update `login.php` to remove "Forgot Password?" link
4. Update `admin/admin_login.php` to remove "Forgot Password?" link (if exists)

