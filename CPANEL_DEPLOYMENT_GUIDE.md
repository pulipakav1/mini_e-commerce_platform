# cPanel Deployment Guide

## Steps to Deploy Your Project in cPanel

### Step 1: Access cPanel
1. Login to your cPanel account
2. Find the "File Manager" tool
3. Navigate to your public_html folder (or the folder you want to use)

### Step 2: Upload Your Files
1. In File Manager, go to public_html (or your domain folder)
2. Create a folder for your project (e.g., "flower_shop" or upload directly to public_html)
3. Upload all PHP files and folders
4. Make sure to upload:
   - All .php files
   - admin folder with all files inside
   - uploads folder (create it if needed for images)
   - images folder if you have category images

**Important Files to Upload:**
- login.php
- signup.php
- home.php
- cart.php
- checkout.php
- add_to_cart.php
- education.php
- order_confirmation.php
- All category pages
- All admin files
- db.php (needs to be updated with your cPanel database info)

### Step 3: Create Database in cPanel
1. Go to "MySQL Databases" in cPanel
2. Create a new database (e.g., "amudalj1_flower_shop")
3. Note the database name - it will be like "username_databasename"
4. Create a database user
5. Add user to database and give ALL PRIVILEGES
6. Note down:
   - Database name
   - Database username
   - Database password
   - Host (usually "localhost")

### Step 4: Update Database Connection
1. Open db.php in File Manager
2. Edit the file and update with your cPanel database info:

```php
<?php
$host = "localhost";
$user = "your_cpanel_db_username";
$pass = "your_cpanel_db_password";
$dbname = "your_cpanel_db_name";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
```

Replace:
- your_cpanel_db_username - with your database username
- your_cpanel_db_password - with your database password
- your_cpanel_db_name - with your database name

### Step 5: Create Database Tables
1. Go to "phpMyAdmin" in cPanel
2. Select your database from the left sidebar
3. Click "SQL" tab
4. Copy and paste the entire contents of tables.sql
5. Click "Go" to execute
6. Verify tables were created (you should see all tables in the left sidebar)

### Step 6: Add Sample Data (Optional but Recommended)
1. Still in phpMyAdmin
2. Click "SQL" tab
3. Run prodandcat.sql to add categories and products
4. Run flower_education_sample_data.sql to add education content

### Step 7: Set File Permissions
1. In File Manager, right-click the uploads folder
2. Select "Change Permissions"
3. Set to 755 (or 777 if needed for uploads)
4. Make sure uploads folder exists and is writable

### Step 8: Test Your Site
1. Visit your domain (e.g., http://yourdomain.com/login.php)
2. Try creating an account
3. Try logging in
4. Test adding items to cart
5. Test checkout process

### Step 9: Create First Admin/Employee Account
1. Go to phpMyAdmin
2. Select your database
3. Click on "employees" table
4. Click "Insert" tab
5. Add an employee manually or use SQL:

```sql
INSERT INTO employees (employee_userid, employee_type, email, employee_password, salary, hire_date) 
VALUES ('admin', 'owner', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 50000, CURDATE());
```

The password hash above is for password "password" - change it after first login.

Or use admin/dab.php to create admin account (make sure to delete this file after use for security).

## Common Issues and Fixes

### Issue: Database Connection Failed
- Check db.php has correct credentials
- Make sure database user has permissions
- Verify database name includes username prefix

### Issue: Images Not Showing
- Check uploads folder exists
- Check folder permissions (755 or 777)
- Verify image paths in database match actual file locations

### Issue: Can't Upload Files
- Check uploads folder permissions
- Check PHP upload_max_filesize in php.ini
- Make sure folder is writable

### Issue: 500 Error
- Check error_log in cPanel
- Verify PHP syntax is correct
- Check file permissions

## File Structure in cPanel

Your files should be organized like this:
```
public_html/
├── login.php
├── signup.php
├── home.php
├── cart.php
├── checkout.php
├── add_to_cart.php
├── education.php
├── db.php
├── admin/
│   ├── admin_login.php
│   ├── dashboard.php
│   ├── products.php
│   └── ... (all admin files)
├── uploads/  (create this folder, set to 755)
└── images/   (if you have category images)
```

## Security Notes

1. Delete admin/dab.php after creating admin account
2. Make sure db.php is not accessible via web browser (it should be fine since it has no HTML output)
3. Keep database credentials secure
4. Regularly backup your database via phpMyAdmin

## Testing Checklist

After deployment, test:
- [ ] User can sign up
- [ ] User can login
- [ ] Products display correctly
- [ ] Can add items to cart
- [ ] Cart displays correctly
- [ ] Checkout works
- [ ] Order is created in database
- [ ] Inventory updates after order
- [ ] Receipt is generated
- [ ] Order history shows orders grouped properly
- [ ] Admin login works
- [ ] Admin can manage products
- [ ] Admin can manage employees
- [ ] Education page displays content

## Database Backup

Before making changes, always backup:
1. Go to phpMyAdmin
2. Select your database
3. Click "Export" tab
4. Choose "Quick" method
5. Click "Go" to download backup

