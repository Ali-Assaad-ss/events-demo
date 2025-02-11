<?php
include '../includes/db.php';

if (isset($_POST['photo_id'])) {
    $photo_id = $_POST['photo_id'];
    $wedding_id = $_POST['wedding_id'];

    if($photo_id =="square"){
        $stmt = $conn->prepare("UPDATE weddings SET square_photo = NULL WHERE id = ?");
        $stmt->bind_param("i", $wedding_id);
        $stmt->execute();
        echo "Square photo deleted successfully";
        exit();
    }
    else{
    // Delete the photo from the database
    $query = "DELETE FROM wedding_images WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $photo_id);

    if ($stmt->execute()) {
        echo "Photo deleted successfully";

    } else {
        echo "Error deleting photo: " . $conn->error;

    }
}

    $stmt->close();
} else {
    // header("Location: view_weddings.php?message=No photo ID provided");
    echo "No photo ID provided";
}

$conn->close();
?>