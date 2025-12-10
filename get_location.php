<?php
include "db.php";

if(isset($_GET['country_id'])) {
    $country_id = intval($_GET['country_id']);
    $stmt = $conn->prepare("SELECT id, name FROM states WHERE country_id=?");
    $stmt->bind_param("i", $country_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $states = [];
    while($row = $result->fetch_assoc()) $states[] = $row;
    echo json_encode($states);
    exit;
}

if(isset($_GET['state_id'])) {
    $state_id = intval($_GET['state_id']);
    $stmt = $conn->prepare("SELECT id, name FROM cities WHERE state_id=?");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cities = [];
    while($row = $result->fetch_assoc()) $cities[] = $row;
    echo json_encode($cities);
    exit;
}

if(isset($_GET['city_id'])) {
    $city_id = intval($_GET['city_id']);
    // Fetch a sample ZIP from the addresses table (or you can have a zip column in cities)
    $stmt = $conn->prepare("SELECT zip FROM addresses WHERE city_id=? LIMIT 1");
    $stmt->bind_param("i", $city_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $zip = $result->fetch_assoc()['zip'] ?? '';
    echo json_encode(['zip' => $zip]);
    exit;
}
