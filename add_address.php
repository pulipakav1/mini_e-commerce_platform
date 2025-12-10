<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// Fetch countries
$countriesResult = $conn->query("SELECT * FROM countries");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country_id = trim($_POST['country']);
    $state_id = trim($_POST['state']);
    $city_id = trim($_POST['city']);
    $zip = trim($_POST['zip']);
    $address_line = trim($_POST['address_line']);

    if (empty($country_id) || empty($state_id) || empty($city_id) || empty($zip) || empty($address_line)) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO addresses (user_id, country_id, state_id, city_id, zip, address_line) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $user_id, $country_id, $state_id, $city_id, $zip, $address_line);
        if ($stmt->execute()) {
            // Redirect to addresses.php after successful insert
            header("Location: addresses.php");
            exit();
        } else {
            $error = "Failed to add address. Try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Address</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial; background:#f5f5f5; margin:0; padding:0; text-align:center; }
.header { display:flex; align-items:center; padding:12px 16px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.back-btn { font-size:20px; cursor:pointer; color:#1d4ed8; text-decoration:none; margin-right:12px;}
.header-title { font-size:18px; font-weight:bold; }
.container { max-width:500px; margin:40px auto; background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
input, select { width:90%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc;}
button { padding:12px 20px; background:#1d4ed8; color:#fff; border:none; border-radius:8px; cursor:pointer; margin-top:10px;}
button:hover { background:#2563eb;}
.error { color:red; margin-bottom:10px; }
</style>
</head>
<body>

<div class="header">
    <a href="addresses.php" class="back-btn">Back to Addresses</a>
    <div class="header-title">Add New Address</div>
</div>

<div class="container">
    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="add_address.php">
        <select name="country" id="country" required onchange="fetchStates()">
            <option value="">Select Country</option>
            <?php while ($row = $countriesResult->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php endwhile; ?>
        </select><br>

        <select name="state" id="state" required onchange="fetchCities()">
            <option value="">Select State</option>
        </select><br>

        <select name="city" id="city" required onchange="fetchZip()">
            <option value="">Select City</option>
        </select><br>

        <input type="text" name="zip" id="zip" placeholder="ZIP Code" required><br>
        <input type="text" name="address_line" placeholder="Address Line" required><br>

        <button type="submit">Add Address</button>
    </form>
</div>

<script>
function fetchStates() {
    let countryId = document.getElementById('country').value;
    fetch(`get_location.php?country_id=${countryId}`)
    .then(res => res.json())
    .then(data => {
        let stateSelect = document.getElementById('state');
        stateSelect.innerHTML = '<option value="">Select State</option>';
        data.forEach(s => {
            let opt = document.createElement('option');
            opt.value = s.id;
            opt.text = s.name;
            stateSelect.add(opt);
        });
        document.getElementById('city').innerHTML = '<option value="">Select City</option>';
        document.getElementById('zip').value = '';
    });
}

function fetchCities() {
    let stateId = document.getElementById('state').value;
    fetch(`get_location.php?state_id=${stateId}`)
    .then(res => res.json())
    .then(data => {
        let citySelect = document.getElementById('city');
        citySelect.innerHTML = '<option value="">Select City</option>';
        data.forEach(c => {
            let opt = document.createElement('option');
            opt.value = c.id;
            opt.text = c.name;
            citySelect.add(opt);
        });
        document.getElementById('zip').value = '';
    });
}

function fetchZip() {
    let cityId = document.getElementById('city').value;
    fetch(`get_location.php?city_id=${cityId}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('zip').value = data.zip || '';
    });
}
</script>

</body>
</html>
