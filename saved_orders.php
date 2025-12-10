<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch saved products
$savedQuery = $conn->prepare("
    SELECT s.id, f.name, f.price, f.image
    FROM saved_products s
    INNER JOIN flowers f ON s.flower_id = f.id
    WHERE s.user_id = ?
    ORDER BY s.id DESC
");

if (!$savedQuery) {
    die("Query Error: " . $conn->error);
}

$savedQuery->bind_param("i", $user_id);
$savedQuery->execute();
$savedResult = $savedQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Saved Products</title>

<style>
body { 
    font-family: Arial; 
    background: #f5f5f5; 
    margin:0; 
    padding:0; 
    text-align:center;
}

/* Header with Back Button */
.header {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top:0;
    z-index: 100;
}

.back-btn {
    font-size: 22px;
    text-decoration: none;
    color: #1d4ed8;
    margin-right: 12px;
}

.header-title {
    font-size: 18px;
    font-weight: bold;
}

/* Product item */
.saved-item {
    background:#fff;
    padding:15px;
    margin:12px auto;
    max-width:400px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.saved-item img {
    width:70px;
    height:70px;
    border-radius:6px;
    object-fit:cover;
}

.saved-item div {
    text-align:left;
}
</style>

</head>
<body>

<!-- Header -->
<div class="header">
    <a href="profile.php" class="back-btn">Back</a>
    <div class="header-title">Saved Products</div>
</div>

<?php if ($savedResult->num_rows > 0): ?>
    <?php while ($row = $savedResult->fetch_assoc()): ?>

        <div class="saved-item">
            <img src="<?php echo (!empty($row['image']) ? $row['image'] : 'no_image.png'); ?>" 
                 alt="<?php echo htmlspecialchars($row['name']); ?>">

            <div>
                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                Price: $<?php echo htmlspecialchars($row['price']); ?>
            </div>
        </div>

    <?php endwhile; ?>

<?php else: ?>
    <p style="margin-top:20px;">No saved products yet.</p>
<?php endif; ?>

</body>
</html>
