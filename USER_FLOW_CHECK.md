# Complete User Flow Verification

## ✅ CUSTOMER USER FLOW

### 1. Entry Point
- **login.php** 
  - ✅ Form submits to self (POST)
  - ✅ Links: signup.php, admin/admin_login.php
  - ✅ Redirects to: home.php (after login)

### 2. Signup Flow
- **signup.php**
  - ✅ Form submits to self (POST)
  - ✅ Links: login.php
  - ✅ Redirects to: login.php?signup=success

### 3. Home Page
- **home.php**
  - ✅ Top bar: Cart (with count) → cart.php, Orders → my_orders.php
  - ✅ Categories link to: home_living.php, cups_bottles.php, style_accessories.php, tulip_collection.php, indoor_plants.php
  - ✅ Education button → education.php
  - ✅ Bottom menu: Home (active), Profile → profile.php

### 4. Category Pages (All 5 pages)
- **home_living.php, cups_bottles.php, style_accessories.php, tulip_collection.php, indoor_plants.php**
  - ✅ Top bar: Cart (with count) → cart.php, Orders → my_orders.php
  - ✅ Forms submit to: add_to_cart.php
  - ✅ Hidden redirect field: returns to same category page
  - ✅ Bottom menu: Home → home.php, Profile → profile.php

### 5. Add to Cart
- **add_to_cart.php**
  - ✅ Processes POST from category pages
  - ✅ Validates redirect: home.php, cart.php, or category pages
  - ✅ Redirects back with message

### 6. Cart Page
- **cart.php**
  - ✅ Shows cart items
  - ✅ Update form submits to self (POST)
  - ✅ Remove links: cart.php?remove=X
  - ✅ Links: checkout.php, home.php
  - ✅ "Back to Shopping" → home.php

### 7. Checkout
- **checkout.php**
  - ✅ Shows cart summary, addresses, payment method
  - ✅ Form submits to self (POST place_order)
  - ✅ Redirects to: order_confirmation.php?order_id=X&receipt=REC-X
  - ✅ Links: cart.php (back)

### 8. Order Confirmation
- **order_confirmation.php**
  - ✅ Shows order details, receipt number
  - ✅ Links: my_orders.php, home.php

### 9. My Orders
- **my_orders.php**
  - ✅ Shows order history grouped by order
  - ✅ Links: home.php

### 10. Profile
- **profile.php**
  - ✅ Shows user info
  - ✅ Links: my_orders.php, logout.php
  - ✅ Bottom menu: Home → home.php, Profile (active)

### 11. Education
- **education.php**
  - ✅ Shows tulip education content
  - ✅ Links: home.php (back)

### 12. Logout
- **logout.php**
  - ✅ Destroys session
  - ✅ Redirects to: login.php (auto after 3 seconds)
  - ✅ Links: login.php

---

## ✅ ADMIN FLOW

### 1. Admin Login
- **admin/admin_login.php**
  - ✅ Form submits to self (POST)
  - ✅ Links: ../login.php (back to customer login)
  - ✅ Redirects to: dashboard.php

### 2. Admin Dashboard
- **admin/dashboard.php**
  - ✅ Links based on role:
    - inventory_manager: products.php, view_products.php
    - business_manager/owner: products.php, orders.php, hr.php
    - owner only: reports.php
  - ✅ Links: logout.php

### 3. Admin Product Management
- **admin/products.php**: Add products → Redirects to view_products.php or dashboard
- **admin/view_products.php**: View/Delete → Links back to products.php
- **admin/edit_product.php**: Edit products → Redirects to products.php

### 4. Admin Orders
- **admin/orders.php**: Manage products (search, delete) → Links to dashboard.php, edit_product.php

### 5. Admin HR
- **admin/hr.php**: View employees → Links to add_employee.php, view_employee.php, edit_employee.php, delete_employee.php

---

## ⚠️ POTENTIAL ISSUES TO CHECK:

1. ✅ All category pages have cart icon with count
2. ✅ All forms have correct action attributes
3. ✅ All redirects are proper
4. ✅ Navigation is consistent
5. ✅ Error handling in place
6. ✅ Session checks on all protected pages

---

## ✅ FLOW DIAGRAM:

```
CUSTOMER FLOW:
login.php → home.php → [Category Page] → add_to_cart.php → [Category Page]
                                                          ↓
                                                    cart.php → checkout.php
                                                                     ↓
                                                          order_confirmation.php
                                                                     ↓
                                                          my_orders.php
                                                                     ↓
                                                          home.php (back)

ADMIN FLOW:
admin/admin_login.php → admin/dashboard.php → [Admin Pages] → admin/logout.php → login.php
```

