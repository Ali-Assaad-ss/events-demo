<?php
include '../includes/db.php';

// Fetch the wedding details to edit
$wedding_id = isset($_GET['uuid']) ? intval($_GET['uuid']) : 0;
if ($wedding_id <= 0) {
    die('Invalid wedding ID');
}
// Fetch wedding details
$stmt = $conn->prepare("SELECT * FROM weddings WHERE uuid = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();
$wedding = $stmt->get_result()->fetch_assoc();
if (!$wedding) {
    die('Wedding not found');
}

// Fetch wedding data
$stmt = $conn->prepare("SELECT * FROM rsvp WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding["id"]);
$stmt->execute();
$guests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest List</title>

    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Include Sortable CSS -->
    <link href="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/dist/sortable.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/tables.css">
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-ring"></i> Guest List</h2>
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group">
                <input type="text" id="search-Guests" class="form-control" placeholder="Search by name..." aria-label="Search" style='max-width:400px; max-height:50px'>
            </div>
            <!-- Export Button -->
            <a href="excel.php?id=<?= $wedding["id"] ?>" class="btn btn-success" style='max-height:50px;font-size: small;'><i class="fas fa-file-excel"></i> Export to Excel</a>
        </div>

        <!-- Custom Invitation Link Section in One Line -->
        <div class="mb-3 d-flex align-items-center" style=gap:10px;>
            <label for="visitor-limit" class="mr-2">Create invitation with limit</label>
            <input type="number" id="visitor-limit" class="form-control" placeholder="limit" min="1" style="width: 100px;" required>
            <button style=width:150px; id="generate-link-btn" class="btn btn-primary ml-2">Copy Link</button>
        </div>

        <!-- Display Generated Invitation Link -->
        <div id="invitation-link-container" style="display:none;">
            <p>Invitation link: <a id="invitation-link" href="#" target="_blank">Invite Link</a></p>
        </div>

        <div class="table-container">
            <table class="table table-hover sortable" id="weddings-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Attending</th>
                        <th>Response Date</th>
                        <th>Event</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; ?>
                    <?php foreach ($guests as $event) : ?>
                        <tr>
                            <td><?= $index++; ?></td>
                            <td><?= htmlspecialchars($event['full_name']); ?></td>
                            <td>
                                <?= $event['attending'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'; ?>
                            </td>
                            <td><?= date("F j, Y, g:i A", strtotime($event['answer_date'])); ?></td>
                            <td> <?= htmlspecialchars($wedding['groom'] . (!empty($wedding['bride']) ? ' & ' . $wedding['bride'] : '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Optional: Use JavaScript to handle form submission and display the generated link dynamically
        document.getElementById("generate-link-btn").addEventListener("click", function(event) {
            const visitorLimit = document.getElementById("visitor-limit").value;
            if (!visitorLimit) {
                alert("Please enter a visitor limit.");
                return;
            }
            const weddingId = <?= $wedding["id"] ?>;
            // Create the custom invitation link (can include visitor limit and wedding_id)
            const baseURL = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ":" + window.location.port : "");
            console.log(baseURL);
            const invitationLink = `${baseURL}/event?id=${weddingId}&l=${visitorLimit}`;
            navigator.clipboard.writeText(invitationLink)
            // Show the generated link to the user
            document.getElementById("invitation-link").href = invitationLink;
            document.getElementById("invitation-link").textContent = invitationLink;
            document.getElementById("invitation-link-container").style.display = "block";
        });
    </script>
    <script src="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/dist/sortable.min.js"></script>
    <!-- Include Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Live Search Script -->
    <script>
        document.getElementById('search-Guests').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            const rows = document.querySelectorAll('#weddings-table tbody tr');
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                if (name.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>