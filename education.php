<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$edu_stmt = $conn->prepare("SELECT education_id, education_section, descriptions FROM flower_education ORDER BY education_id");
$edu_stmt->execute();
$edu_result = $edu_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tulip Education</title>
<style>
body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 0; }
.container { max-width: 1000px; margin: 20px auto; padding: 20px; }
h1 { text-align: center; color: #1d4ed8; margin-bottom: 30px; }
.back-link { margin-bottom: 20px; }
.back-link a { color: #1d4ed8; text-decoration: none; font-weight: bold; }
.education-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); margin-bottom: 25px; }
.education-card h2 { color: #1d4ed8; margin-top: 0; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; }
.education-card p { line-height: 1.6; color: #555; margin-bottom: 15px; }
.info-section { margin-top: 20px; }
</style>
</head>
<body>

<div class="container">
    <div class="back-link">
        <a href="home.php">← Back to Home</a>
    </div>
    
    <h1>Tulip Education</h1>
    <p style="text-align: center; color: #666; margin-bottom: 30px;">Learn about tulips</p>
    
    <?php 
    if ($edu_result->num_rows > 0): 
        while ($edu = $edu_result->fetch_assoc()): 
    ?>
        <div class="education-card">
            <h2><?php echo htmlspecialchars($edu['education_section']); ?></h2>
            <div class="info-section">
                <p><?php echo nl2br(htmlspecialchars($edu['descriptions'])); ?></p>
            </div>
        </div>
    <?php 
        endwhile;
    else: 
    ?>
        <div class="education-card">
            <p style="text-align: center; color: #666;">No education content available yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

