<?php
session_start(); // Start session to store uploaded filename

$message = "";
$target_dir = "img-sys/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

if (isset($_POST['submit'])) {
    if (!isset($_FILES["fileToUpload"]) || $_FILES["fileToUpload"]["error"] == 4) {
        $message = "No file selected.";
    } else {
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;

        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check === false) {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            $message = "File already exists. It will be overwritten.";
        }

        if ($_FILES["fileToUpload"]["size"] > 2000000) {
            $message = "File is too large.";
            $uploadOk = 0;
        }

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg','jpeg','png','gif'])) {
            $message = "Only JPG, JPEG, PNG & GIF files allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $message = "File uploaded successfully!";
                $_SESSION['profile_image'] = $target_file; // Store filename in session
            } else {
                $message = "Error uploading file.";
            }
        }
    }
}
?>

<h2>Upload Profile Image</h2>
<form action="upload_profile.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="fileToUpload" required>
    <input type="submit" value="Upload Image" name="submit">
</form>
<p><?php echo $message; ?></p>
