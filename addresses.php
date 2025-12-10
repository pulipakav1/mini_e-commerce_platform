<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all addresses for logged-in user
$stmt = $conn->prepare("
    SELECT a.id, a.address_line, a.zip, c.name AS city, s.name AS state, co.name AS country
    FROM addresses a
    JOIN cities c ON a.city_id = c.id
    JOIN states s ON a.state_id = s.id
    JOIN countries co ON a.country_id = co.id
    WHERE a.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Addresses</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial; margin:0; padding:20px; background:#f5f5f5; }
.header { display:flex; align-items:center; margin-bottom:20px; }
.back-btn { font-size:18px; padding:6px 12px; background:#1d4ed8; color:#fff; text-decoration:none; border-radius:6px; margin-right:12px; }
.back-btn:hover { background:#2563eb; }
h2 { color:#1d4ed8; display:inline-block; margin:0; }
.address { background:#fff; padding:15px; margin-bottom:10px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.add-btn { display:inline-block; padding:10px 15px; background:#1d4ed8; color:#fff; text-decoration:none; border-radius:6px; margin-bottom:15px;}
.add-btn:hover { background:#2563eb;}
</style>
</head>
<body>

<div class="header">
    <a href="profile.php" class="back-btn">Back to Profile</a>
    <h2>My Addresses</h2>
</div>

<a href="add_address.php" class="add-btn">+ Add New Address</a>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="address">
            <strong>Address:</strong> <?php echo $row['address_line']; ?><br>
            <strong>ZIP:</strong> <?php echo $row['zip']; ?><br>
            <strong>City:</strong> <?php echo $row['city']; ?><br>
            <strong>State:</strong> <?php echo $row['state']; ?><br>
            <strong>Country:</strong> <?php echo $row['country']; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No addresses found. Click "Add New Address" to create one.</p>
<?php endif; ?>

</body>
</html>
