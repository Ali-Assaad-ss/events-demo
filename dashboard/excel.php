<?php
include '../includes/db.php';

// Set headers to force download as Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="guest_list.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Fetch the guest list data
$wedding_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT full_name, attending, answer_date FROM rsvp WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();
$result = $stmt->get_result();

// Output Excel Table
echo "<table border='1'>";
echo "<tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Attending</th>
        <th>Response Date</th>
      </tr>";

$index = 1;
while ($row = $result->fetch_assoc()) {
    $attending = $row['attending'] ? 'Yes' : 'No';
    $response_date = date("F j, Y, g:i A", strtotime($row['answer_date']));
    echo "<tr>
            <td>{$index}</td>
            <td>" . htmlspecialchars($row['full_name']) . "</td>
            <td>{$attending}</td>
            <td>{$response_date}</td>
          </tr>";
    $index++;
}
echo "</table>";

$conn->close();
?>
