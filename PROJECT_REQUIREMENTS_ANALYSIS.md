# Project Requirements Analysis

## ❌ CRITICAL MISSING FEATURES

### 1. **Shopping Cart System** - NOT IMPLEMENTED
**Requirement:** "Implement a shopping cart for your site. It will need to record the items the user wishes to buy, allow the user to buy as many products as they want (so long as they are in inventory), complete the transaction, record the receipt, update inventory and store the receipt persistently."

**Current Status:** 
- Products are displayed but there's NO "Add to Cart" functionality
- NO cart.php or shopping_cart.php file exists
- Users cannot add products to a cart
- No checkout process exists

**Files Needed:**
- `cart.php` - Display cart items
- `add_to_cart.php` - Add products to cart
- `checkout.php` - Process order, update inventory, create receipt
- Database table: `cart` or `cart_items`

---

### 2. **Flower Education Section** - NOT IMPLEMENTED
**Requirement:** "Have a section of the site that educates the public about the flower. All content on the website that is displayed must be in the database"

**Current Status:**
- NO educational content about flowers exists
- NO database table for educational content
- NO page displaying flower information

**Files Needed:**
- `flowers_info.php` or `education.php` - Display flower educational content
- Database table: `flower_education` or `flower_info` with columns like:
  - id, flower_name, description, care_instructions, origin, symbolism, etc.

---

### 3. **Inventory Management During Purchase** - NOT IMPLEMENTED
**Requirement:** "allow the user to buy as many products as they want (so long as they are in inventory)"

**Current Status:**
- Products have a `quantity` field in database
- BUT: No validation checks if quantity is available before purchase
- No inventory decrement when orders are placed
- Products can be viewed but quantity isn't checked

**Required Implementation:**
- Check `quantity > 0` before allowing "Add to Cart"
- Validate cart quantity doesn't exceed available inventory
- Update `products.quantity` when order is completed

---

### 4. **Order Completion & Receipt** - NOT IMPLEMENTED
**Requirement:** "complete the transaction, record the receipt, update inventory and store the receipt persistently"

**Current Status:**
- `my_orders.php` displays orders but:
  - NO checkout process exists
  - NO receipt generation
  - Orders table may exist but no way to create orders from cart
  - Inventory is NOT updated

**Files Needed:**
- `checkout.php` - Process payment, create order, generate receipt
- Receipt display with order details
- Order should link to `order_items` table

---

### 5. **Order Grouping by Instance** - NOT IMPLEMENTED
**Requirement:** "Users should also be able to see previous orders grouped by order instance (look at how amazon does it)"

**Current Status:**
- `my_orders.php` shows a flat list of orders
- No grouping by order date/order_id
- Need to see all items in one order together

**Required Implementation:**
- Group orders by `order_id` or `order_date`
- Display order header with date/time
- Show all items in that order below
- Need `order_items` table to link products to orders

---

### 6. **CRITICAL VIOLATION: Credit Card Storage** - VIOLATES REQUIREMENTS
**Requirement:** "NO CREDIT CARDS SHOULD BE ACCEPTED. If numbers are accepted, points will be taken off. Accepting numeric characters will result in a letter grade deduction."

**Current Status:**
- `payment.php` lines 25-32 STORES credit card information:
  - `card_number` is stored
  - `expiry` is stored  
  - `cvv` is stored

**THIS MUST BE REMOVED IMMEDIATELY!**

**Fix Required:**
- Remove "Card Payment" option
- Only allow "Cash on Delivery"
- Remove all card_number, expiry, cvv fields

---

## ✅ IMPLEMENTED FEATURES

### 1. **E-commerce Shop with Database** - ✅ PARTIALLY
- Products stored in `products` table
- Categories stored in `category` table
- Products displayed from database
- 5 categories exist: Home & Living, Cups & Bottles, Style Accessories, Tulip Collection, Indoor Plants
- **ISSUE:** Need to verify 30+ products exist in database

### 2. **HR Section with Employee Types** - ✅ IMPLEMENTED
- `admin/hr.php` - HR management
- `admin/add_employee.php` - Add employees
- `admin/edit_employee.php` - Edit employees
- `admin/delete_employee.php` - Delete employees
- `admin/view_employee.php` - View employee details
- Employee roles: owner, hr, employee (3 types)
- Salary tracking in database

### 3. **User Groups/Roles** - ✅ PARTIALLY
**Current Roles:**
1. **Client/Customer** - Can login, view products, (should) add to cart, view orders
2. **Employee** - Admin login, can manage products (depending on role)
3. **HR** - Can view/manage employees
4. **Owner** - Full access to everything
5. **Database Administrator** - NOT EXPLICITLY IMPLEMENTED (but exists via phpMyAdmin)

**Missing:** 
- Clear role-based views documentation
- DBA view/interface (though they use phpMyAdmin which is not allowed to count)

### 4. **Security** - ✅ MOSTLY IMPLEMENTED
- ✅ Login required (login.php is first page)
- ✅ Passwords hashed with `password_hash()`
- ✅ SQL Injection protection (after fixes - prepared statements)
- ✅ Session management
- ✅ Access control for admin pages

### 5. **Product Management (CRUD)** - ✅ IMPLEMENTED
- ✅ **Search:** `admin/orders.php` has search functionality
- ✅ **Insert:** `admin/products.php` - Add products
- ✅ **Delete:** `admin/orders.php` - Delete products
- ✅ **Update:** `admin/edit_product.php` - Edit products
- ✅ Inventory tracking (quantity field exists)

**ISSUE:** Inventory is tracked but NOT enforced during purchase

### 6. **Order History** - ✅ PARTIALLY
- ✅ `my_orders.php` shows order history
- ✅ Orders displayed by date/time
- ❌ NOT grouped by order instance
- ❌ No receipt details

### 7. **Address Management** - ✅ IMPLEMENTED
- ✅ `addresses.php` - View addresses
- ✅ `add_address.php` - Add addresses
- ✅ Uses countries, states, cities tables

---

## 📋 DATABASE STRUCTURE NEEDED

### Missing Tables:
1. **`cart`** or **`cart_items`**
   - id, user_id, product_id, quantity, created_at

2. **`orders`** (may exist but needs verification)
   - id, user_id, order_date, total_amount, status, address_id

3. **`order_items`**
   - id, order_id, product_id, quantity, price_at_purchase

4. **`receipts`** or use orders table
   - receipt_number, order_id, generated_at

5. **`flower_education`** or **`flower_info`**
   - id, flower_name, description, care_instructions, origin, symbolism, image

### Tables That Should Exist (need verification):
- ✅ `users` - Customer accounts
- ✅ `admins` - Employee accounts  
- ✅ `products` - Product catalog
- ✅ `category` - Product categories
- ✅ `orders` - Order records (may need modification)
- ✅ `addresses` - User addresses
- ✅ `countries`, `states`, `cities` - Location data
- ✅ `payment_methods` - Payment info (NEEDS MODIFICATION - remove card fields)
- ❓ `images` - Product images (referenced in code)
- ❓ `cart_items` - Shopping cart (NOT FOUND)

---

## 🔧 CRITICAL FIXES NEEDED

### IMMEDIATE (Grade-Destroying):
1. **Remove Credit Card Storage** (payment.php)
   - Remove "Card Payment" option
   - Remove card_number, expiry, cvv fields
   - Only allow "Cash on Delivery"

2. **Implement Shopping Cart**
   - Create cart.php
   - Create add_to_cart.php
   - Create checkout.php
   - Create cart_items table

3. **Implement Inventory Checking**
   - Check quantity before add to cart
   - Check quantity before checkout
   - Update inventory after order

4. **Implement Order Completion**
   - Create order from cart
   - Generate receipt
   - Update inventory
   - Clear cart

5. **Add Flower Education Section**
   - Create flower_education table
   - Create education.php page
   - Populate with flower information

6. **Fix Order History Grouping**
   - Group orders by order instance
   - Show all items per order
   - Create order_items table if missing

---

## 📊 REQUIREMENTS CHECKLIST

| Requirement | Status | Notes |
|------------|--------|-------|
| Flower education section | ❌ MISSING | Need database table + page |
| E-commerce shop (30+ products, 5 categories) | ⚠️ PARTIAL | Categories exist, verify 30 products |
| HR section (3+ employee types) | ✅ DONE | owner, hr, employee |
| Public/shopper views | ⚠️ PARTIAL | Missing cart, checkout, education |
| Employee views | ✅ DONE | Admin dashboard, products, orders, HR |
| 4 user groups identified | ⚠️ PARTIAL | Need DBA view explicitly |
| E-R diagram | ❓ | Need to create/verify |
| Views for each user type (4+) | ⚠️ PARTIAL | Need documentation |
| Search, Insert, Delete, Modify | ✅ DONE | All CRUD operations exist |
| Inventory management | ⚠️ PARTIAL | Tracked but not enforced |
| Receipt management | ❌ MISSING | No receipt generation |
| Order history by date/time | ✅ DONE | my_orders.php exists |
| Order grouping by instance | ❌ MISSING | Flat list, needs grouping |
| Shopping cart | ❌ MISSING | Critical missing feature |
| Transaction completion | ❌ MISSING | No checkout process |
| Inventory update on purchase | ❌ MISSING | Not implemented |
| Login required | ✅ DONE | All pages protected |
| Password hashing | ✅ DONE | Using password_hash() |
| SQL Injection protection | ✅ DONE | Prepared statements (after fixes) |
| No credit cards | ❌ VIOLATION | Currently stores card data |
| Personal data security | ⚠️ PARTIAL | Need documentation |
| C-Panel implementation | ✅ DONE | Using MySQL |

---

## 🎯 PRIORITY FIXES (In Order)

1. **REMOVE CREDIT CARD STORAGE** (Immediate - prevents grade deduction)
2. **Implement Shopping Cart System** (Critical - core requirement)
3. **Implement Checkout Process** (Critical - complete transaction)
4. **Add Inventory Validation** (Critical - enforce inventory limits)
5. **Create Flower Education Section** (Required feature)
6. **Fix Order History Grouping** (Required feature)
7. **Create Documentation** (Required - views, E-R diagram, dataflow)

---

## 📝 DATAFLOW DIAGRAM NEEDED

You need to document the dataflow for:
1. **Customer Purchase Flow:**
   - Login → Browse Products → Add to Cart → Checkout → Payment → Order Created → Receipt → Inventory Updated

2. **Admin Product Management:**
   - Login → Dashboard → Add/Edit/Delete Products → Database Updated

3. **HR Employee Management:**
   - Login (owner/hr) → HR Section → Add/Edit/Delete Employees → Database Updated

4. **Order Processing:**
   - Customer Order → Create Order Record → Create Order Items → Update Inventory → Generate Receipt

---

## 📚 DOCUMENTATION NEEDED

1. **E-R Diagram** - Visual representation of all tables and relationships
2. **User Role Documentation** - For each role (DBA, Owner, Employee, Client):
   - Job description
   - Data access
   - Screenshots of views
3. **Dataflow Diagrams** - Show how data moves through the system
4. **Query Documentation** - For each CRUD operation, document:
   - The SQL query
   - How it works
   - Screenshots
5. **Sensitive Data Handling** - Document how personal data is protected
6. **Screenshots** - One paragraph description per screenshot

---

## ⚠️ GRADE IMPACT

Based on requirements:
- **Maximum Grade Without Cart:** D (core feature missing)
- **Maximum Grade With Credit Cards:** B (but significant deduction)
- **Current Estimated Grade:** F (missing cart, storing cards, missing education section)

**To Achieve A Grade:**
1. Fix all critical missing features
2. Remove credit card storage
3. Complete documentation
4. Verify 30+ products exist
5. Create proper dataflow diagrams

