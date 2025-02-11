<?php
include '../includes/db.php';

$wedding_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM rsvp WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();



// Delete events in the wedding
$stmt = $conn->prepare("DELETE FROM events WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();


// Delete the wedding from the database
$stmt = $conn->prepare("DELETE FROM weddings WHERE id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();

header("Location: view_weddings.php");
exit;
?>