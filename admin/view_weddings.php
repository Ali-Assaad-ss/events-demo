<?php
include '../includes/db.php';



// Fetch all events
$stmt = $conn->prepare("SELECT * FROM weddings ORDER BY created_at DESC");
$stmt->execute();
$guests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tables.css">
    <link href="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/dist/sortable.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/dist/sortable.min.js"></script>
    <script src="script.js"></script>
    <title>Events List</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


</head>

<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; color: black; width:100%;height:80px;position:fixed;top:0;z-index:100;border-bottom: 1px solid #8b7e6f;border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;">
        <img style=" margin-inline:20px; cursor:pointer" src="events-Logo.png" width="200px" height=130px onclick="window.location.href='view_weddings.php'">
        <button onclick="window.location.href='logout.php'" style="padding:10px 10px; background-color:#baaf11ab; color: white; border: none; cursor: pointer; width:120px; margin-inline:20px; border-radius:10px">Logout</button>
    </header>
    <div class="container" style="margin-top:100px">
        <h2><i class="fas fa-ring"></i> Events List</h2>
        <input type="text" id="search-weddings" placeholder="Search events..." class="form-control" />
        <div style="width:100%; display: flex;">

            <button style="margin:10px;margin-left:auto; width:150px;border-radius:15px;color:white;" onclick="window.location.href='create_wedding.php';">Add event</button>
        </div>

        <table class="table table-hover sortable" id="weddings-table">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Names</th>
                    <th>Creation Date</th>
                    <th>Website URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guests as $event) : ?>
                    <tr>
                        <td><?= htmlspecialchars($event['id']) ?></td>
                        <!-- if bride exist -->

                        <td> <?= htmlspecialchars($event['groom'] . (!empty($event['bride']) ? " & " . $event['bride'] : "")) ?></td>
                        <td><?= htmlspecialchars($event['created_at']) ?></td>
                        <td>
                            <a href="../event/index.php?id=<?= $event['id'] ?>&l=5" target="_blank" class="btn btn-link"><i class="fas fa-link"></i>card</a>
                            <a href="../dashboard?uuid=<?= $event['uuid'] ?>" target="_blank" class="btn btn-link"><i class="fas fa-link"></i> guest list</a>

                        </td>
                        <td class="action-links">
                            <a href="edit_wedding.php?id=<?= $event['id'] ?>" class="text-success"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_wedding.php?id=<?= $event['id'] ?>" class="text-danger" onclick="return confirm('Are you sure you want to delete this event?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>