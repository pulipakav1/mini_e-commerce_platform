<?php
session_start();
include "db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";
$action = $_GET['action'] ?? 'view';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        // Verify current password
        $check_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if (password_verify($current_password, $check_row['password'])) {
            // Update password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $hashed, $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Password changed successfully!";
                $action = 'view';
            } else {
                $error = "Failed to update password: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            $error = "Current password is incorrect!";
        }
    }
}

// Handle address update/add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['update_address']) || isset($_POST['add_address']))) {
    $street_address = trim($_POST['street_address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    
    // Combine all address components into single address field
    $full_address = trim($street_address . ", " . $city . ", " . $state . " " . $zip_code);
    
    // Update the single address field
    $update_stmt = $conn->prepare("UPDATE users SET address = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $full_address, $user_id);
    
    if ($update_stmt->execute()) {
        $success = "Address " . (isset($_POST['add_address']) ? 'added' : 'updated') . " successfully!";
        $action = 'view';
    } else {
        $error = "Failed to update address: " . $update_stmt->error;
    }
    $update_stmt->close();
}

// Fetch user data
$sql = "SELECT name, email, phone_number, address, user_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$name  = htmlspecialchars($user['name']);
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Not provided';
$phone = isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : 'Not provided';
$username = isset($user['user_name']) ? htmlspecialchars($user['user_name']) : 'Not provided';
$address = isset($user['address']) ? htmlspecialchars($user['address']) : 'Not provided';

// Parse address into components (format: "street, city, state zip")
$street_address = '';
$city = '';
$state = '';
$zip_code = '';

if ($address && $address != 'Not provided') {
    // Try to parse address: "Street Address, City, State Zip"
    $address_parts = explode(',', $address);
    if (count($address_parts) >= 3) {
        $street_address = trim($address_parts[0]);
        $city = trim($address_parts[1]);
        $state_zip = trim($address_parts[2]);
        // Split state and zip (assuming format "State Zip")
        $state_zip_parts = preg_split('/\s+/', $state_zip, 2);
        $state = $state_zip_parts[0] ?? '';
        $zip_code = $state_zip_parts[1] ?? '';
    } elseif (count($address_parts) == 2) {
        $street_address = trim($address_parts[0]);
        $city = trim($address_parts[1]);
    } else {
        $street_address = $address;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: Arial; background:#f5f5f5; margin:0; min-height: 100vh; }

    .hero-image-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 0;
        overflow: hidden;
    }

    .hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.3) 100%);
        z-index: 1;
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }
    .profile-top { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:15px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:20px; border-radius:8px; }
    .profile-info { display:flex; align-items:center; }
    .logout-btn { background:#e63946; padding:8px 14px; color:#fff; border:none; border-radius:8px; cursor:pointer; }
    .profile-section { margin:20px; }
    .profile-card { background:#fff; padding:18px 20px; border-radius:14px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:12px; }
    .profile-card.clickable { display:flex; justify-content:space-between; cursor:pointer; transition: all 0.2s; }
    .profile-card.clickable:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.12); }
    .user-details { background:#fff; padding:20px; border-radius:14px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:20px; }
    .detail-row { display:flex; padding:12px 0; border-bottom:1px solid #eee; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { font-weight:bold; color:#666; width:150px; }
    .detail-value { color:#333; flex:1; }
    .form-group { margin-bottom:15px; }
    .form-group label { display:block; margin-bottom:5px; font-weight:bold; color:#333; }
    .form-group input, .form-group select { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box; }
    .btn { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-size:14px; margin-right:10px; }
    .btn-primary { background:#1d4ed8; color:white; }
    .btn-primary:hover { background:#1e40af; }
    .btn-secondary { background:#6b7280; color:white; }
    .btn-secondary:hover { background:#4b5563; }
    .error { background:#fee; color:#c33; padding:10px; border-radius:6px; margin-bottom:15px; }
    .success { background:#efe; color:#3c3; padding:10px; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>

<!-- Full Page Hero Image -->
<?php
$tulip_image = "images/tulip-field.jpg";
?>
<div class="hero-image-container">
    <?php if (file_exists($tulip_image) || file_exists("images/tulip-field.jpg")): ?>
        <img src="images/tulip-field.jpg" alt="Tulip Field" class="hero-image" onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)';">
    <?php else: ?>
        <div style="width:100%; height:100%; background:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">

<div style="max-width:800px; margin:20px auto; padding:0 20px;">

<!-- TOP SECTION -->
<div class="profile-top">
    <div class="profile-info">
        <div>
            <div style="font-weight:bold; font-size:18px;"><?php echo $name; ?></div>
            <div style="font-size:13px; color:#555;"><?php echo $email; ?></div>
        </div>
    </div>
    <button class="logout-btn" onclick="location.href='auth.php?action=logout'">Logout</button>
</div>

<?php if ($error != ""): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success != ""): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($action == 'view'): ?>
<!-- USER DETAILS SECTION -->
<div class="profile-section">
    <div class="user-details">
        <h3 style="margin-top:0; margin-bottom:20px; color:#1d4ed8;">User Information</h3>
        
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value"><?php echo $name; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Username:</div>
            <div class="detail-value"><?php echo $username; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Email:</div>
            <div class="detail-value"><?php echo $email; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Phone:</div>
            <div class="detail-value"><?php echo $phone; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Address:</div>
            <div class="detail-value">
                <?php if ($street_address || $city || $state || $zip_code): ?>
                    <?php if ($street_address) echo htmlspecialchars($street_address) . '<br>'; ?>
                    <?php if ($city || $state || $zip_code): ?>
                        <?php 
                        $address_line = [];
                        if ($city) $address_line[] = htmlspecialchars($city);
                        if ($state) $address_line[] = htmlspecialchars($state);
                        if ($zip_code) $address_line[] = htmlspecialchars($zip_code);
                        echo implode(', ', $address_line);
                        ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo nl2br($address); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- MENU OPTIONS -->
    <div class="profile-card clickable" onclick="location.href='profile.php?action=change_password'">
        <span>Change Password</span>
        <span style="color:#1d4ed8;">→</span>
    </div>
    
    <div class="profile-card clickable" onclick="location.href='profile.php?action=manage_address'">
        <span>Manage Address</span>
        <span style="color:#1d4ed8;">→</span>
    </div>
    
    <div class="profile-card clickable" onclick="location.href='my_orders.php'">
        <span>My Orders</span>
        <span style="color:#1d4ed8;">→</span>
    </div>
</div>

<?php elseif ($action == 'change_password'): ?>
<!-- CHANGE PASSWORD -->
<div class="user-details">
    <h3 style="margin-top:0; margin-bottom:20px; color:#1d4ed8;">Change Password</h3>
    <form method="POST">
        <div class="form-group">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>New Password:</label>
            <input type="password" name="new_password" required>
        </div>
        <div class="form-group">
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        <button type="button" class="btn btn-secondary" onclick="location.href='profile.php'">Cancel</button>
    </form>
</div>

<?php elseif ($action == 'manage_address' || $action == 'add_address'): ?>
<!-- MANAGE ADDRESS -->
<div class="user-details">
    <h3 style="margin-top:0; margin-bottom:20px; color:#1d4ed8;"><?php echo $action == 'add_address' ? 'Add New Address' : 'Manage Address'; ?></h3>
    <form method="POST">
        <div class="form-group">
            <label>Street Address:</label>
            <input type="text" name="street_address" value="<?php echo htmlspecialchars($street_address); ?>" required>
        </div>
        <div class="form-group">
            <label>City:</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
        </div>
        <div class="form-group">
            <label>State:</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($state); ?>" required>
        </div>
        <div class="form-group">
            <label>Zip Code:</label>
            <input type="text" name="zip_code" value="<?php echo htmlspecialchars($zip_code); ?>" required>
        </div>
        <button type="submit" name="<?php echo $action == 'add_address' ? 'add_address' : 'update_address'; ?>" class="btn btn-primary"><?php echo $action == 'add_address' ? 'Add Address' : 'Update Address'; ?></button>
        <button type="button" class="btn btn-secondary" onclick="location.href='profile.php'">Cancel</button>
    </form>
</div>
<?php endif; ?>

<div style="text-align: center; margin: 30px 0;">
    <button onclick="history.back();" style="padding: 10px 20px; background: #1d4ed8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">← Back</button>
</div>

</div>

</div>
<!-- End Content Wrapper -->

</body>
</html>
