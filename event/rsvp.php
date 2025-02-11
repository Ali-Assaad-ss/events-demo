<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../includes/db.php'; // Database connection file

    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Check database connection
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
    }

    // Retrieve wedding_id and validate
    $wedding_id = isset($_POST['wedding_id']) ? intval($_POST['wedding_id']) : 0;
    if ($wedding_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Valid wedding ID is required."]);
        exit;
    }

    // Guests value: attending or not
    $guests = $_POST['guests'] ?? 'na';
    if (!in_array($guests, ['na', '1'])) {
        echo json_encode(["status" => "error", "message" => "Invalid guests value provided."]);
        exit;
    }

    // Debug input values
    error_log("Wedding ID: $wedding_id | Guests: $guests");

    // Prepare insert statement outside the loops to avoid overhead
    $stmt = $conn->prepare("INSERT INTO rsvp (full_name, attending, wedding_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]));
    }

    // Non-attending case
    if ($guests === 'na') {
        $full_name = trim($_POST['full_name'] ?? '');
        if (empty($full_name)) {
            echo json_encode(["status" => "error", "message" => "Full name is required for non-attending guests."]);
            exit;
        }

        $status = 0; // 0 = not attending
        $stmt->bind_param("sii", $full_name, $status, $wedding_id);

        if ($stmt->execute()) {
            error_log("Non-attending guest '$full_name' inserted successfully.");
        } else {
            die(json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]));
        }
    } 
    // Attending case
    elseif ($guests === '1') {
        $npersons = isset($_POST['npersons']) ? intval($_POST['npersons']) : 0;

        if ($npersons <= 0) {
            echo json_encode(["status" => "error", "message" => "Number of persons must be greater than 0."]);
            exit;
        }

        for ($i = 1; $i <= $npersons; $i++) {
            $full_name = trim($_POST["full_name$i"] ?? '');
            if (empty($full_name)) {
                error_log("Full name for person $i is empty; skipping.");
                continue;
            }

            $status = 1; // 1 = attending
            $stmt->bind_param("sii", $full_name, $status, $wedding_id);

            if ($stmt->execute()) {
                error_log("Attending guest '$full_name' inserted successfully.");
            } else {
                die(json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]));
            }
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Success response
    echo json_encode(["status" => "success", "message" => "RSVP data stored successfully."]);
}
?>
