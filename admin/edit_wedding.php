<?php
include '../includes/db.php';

// Fetch the wedding details to edit
$wedding_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($wedding_id <= 0) {
    die('Invalid wedding ID');
}

// Fetch wedding data
$stmt = $conn->prepare("SELECT * FROM weddings WHERE id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) {
    die('Wedding not found');
}

// Fetch background photos
$stmt = $conn->prepare("SELECT * FROM wedding_images WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();
$background_photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch events
$stmt = $conn->prepare("SELECT * FROM events WHERE wedding_id = ?");
$stmt->bind_param("i", $wedding_id);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (isset($_POST['wedding-check'])) {
        $type = 'wedding';
        $groom = $_POST['groom'];
        $bride = $_POST['bride'];
        $page1 = null;
    } else {
        $type = 'event';
        $groom = $_POST['event-name'];
        $bride = '';
        $page1 = $_POST['page1'];
    }

    $rsvp_date = $_POST['rsvp_date'];
    $ending_quote = $_POST['ending-quote'];

    if (isset($_POST['quote-check'])) {
        $quote = $_POST['quote'];
    } else {
        $quote = '';
    }
    if (isset($_POST['gift-check'])) {
        $gift = $_POST['gift'];
    } else {
        $gift = '';
    }

    // Update wedding data
    $stmt = $conn->prepare("UPDATE weddings SET groom = ?, bride = ?, quote = ?, gift=? ,  rsvp_date=? ,type=?,page1=?,ending_quote=? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $groom, $bride, $quote, $gift, $rsvp_date, $type, $page1, $ending_quote, $wedding_id);
    $stmt->execute();

    // Handle new background photos
    $upload_dir = '../uploads/images/';
    $uploaded_files = [];

    if (isset($_FILES['background_photos'])) {
        foreach ($_FILES['background_photos']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['background_photos']['name'][$key]);
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('', true) . '.' . $file_ext;

            if (move_uploaded_file($tmp_name, $upload_dir . $new_file_name)) {
                $uploaded_files[] = $upload_dir . $new_file_name;
                $stmt = $conn->prepare("INSERT INTO wedding_images (wedding_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $wedding_id, $uploaded_files[count($uploaded_files) - 1]);
                $stmt->execute();
            }
        }
    }

    // Handle new square photo
    if (isset($_FILES['square_photo']) && $_FILES['square_photo']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['square_photo']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('square_', true) . '.' . $file_ext;

        if (move_uploaded_file($_FILES['square_photo']['tmp_name'], $upload_dir . $new_file_name)) {
            $square_photo_path = $upload_dir . $new_file_name;
            $stmt = $conn->prepare("UPDATE weddings SET square_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $square_photo_path, $wedding_id);
            $stmt->execute();
        }
    }
    // Handle new music
    $music_dir = '../uploads/music/';
    if (isset($_FILES['bgmusic']) && $_FILES['bgmusic']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['bgmusic']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('music_', true) . '.' . $file_ext;

        if (move_uploaded_file($_FILES['bgmusic']['tmp_name'], $music_dir . $new_file_name)) {
            $music_path = $music_dir . $new_file_name;
            $stmt = $conn->prepare("UPDATE weddings SET music = ? WHERE id = ?");
            $stmt->bind_param("si", $music_path, $wedding_id);
            $stmt->execute();
        }
    }

    // Update or insert events
    $stmt = $conn->prepare("DELETE FROM events WHERE wedding_id = ?");
    $stmt->bind_param("i", $wedding_id);
    $stmt->execute();

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'event_name') !== false) {
            $event = [
                'name' => $value,
                'date' => $_POST[str_replace('event_name', 'event_date', $key)],
                'time' => $_POST[str_replace('event_name', 'event_time', $key)],
                'location_name' => $_POST[str_replace('event_name', 'event_location_name', $key)],
                'location_url' => $_POST[str_replace('event_name', 'event_location_url', $key)],
            ];

            $stmt = $conn->prepare("INSERT INTO events (wedding_id, name, date, time, location_name, location_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $wedding_id, $event['name'], $event['date'], $event['time'], $event['location_name'], $event['location_url']);
            $stmt->execute();
        }
    }

    // header("Location: view_weddings.php");
    // exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&amp;family=Lora:wght@400;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Sofia|Slabo|Roboto|Inconsolata|Ubuntu" rel="stylesheet">
    <link rel="stylesheet" href="create_wedding.css">
    <link rel="stylesheet" href="edit_wedding.css">
</head>

<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; color: black; width:100%;height:60px;position:fixed;top:0;z-index:100;border-bottom: 1px solid #8b7e6f;">
        <img style=" margin-inline:20px; cursor:pointer" src="events-Logo.png" width="200px" height=130px onclick="window.location.href='view_weddings.php'">
        <button onclick="window.location.href='logout.php'" style="padding:10px 10px; background-color:#baaf11ab; color: white; border: none; cursor: pointer; width:120px; margin-inline:20px;">Logout</button>
    </header>



    <div id=left>


        <form action="" method="POST" enctype="multipart/form-data" id=wedding-form>
            <h1>Edit Event</h1>



            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="wedding-check" name="wedding-check" <?= $event['type'] == "wedding" ? 'checked' : ''; ?>>
                <span class="custom-checkbox"></span>
                <p>Wedding</p>
            </label>

            <div id="couples-name" style="display: <?= $event['type'] == "wedding" ? 'block' : 'none'; ?>;">
                <label>Couple Names:</label><br>
                <div class=couples-names>
                    <input type="text" name="groom" placeHolder="Groom" value=<?= $event['groom'] ?> required><br>
                    <input type="text" name="bride" placeholder="Bride" value=<?= $event['bride'] ?> required><br>
                </div>

            </div>
            <!-- page 1 -->

            <div id=page1-div style="display: <?= $event['type'] == "event" ? 'block' : 'none'; ?>;">
                <label>Event Name</label><br>
                <input type="text" name=event-name placeHolder="event's name" value=<?= $event['groom'] ?>>
                <label>First Page:</label><br>
                <div id="page1-container"></div>
                <textarea name="page1" id="page1-content" style="display:none;"></textarea>
                <br>
            </div>
            <!-- quote -->
            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="quote-check" name="quote-check" <?= $event['quote'] != "" ? 'checked' : ''; ?>>
                <span class="custom-checkbox"></span>
                <p>Quote:</p>

            </label>
            <div id=quote-div style="display: <?= $event['quote'] != "" ? 'block' : 'none'; ?>;">
                <div id="quote-container"></div>
                <textarea name="quote" id="quote-content" style="display:none;"></textarea>
                <br>
            </div>
            <!-- end quote -->

            <!-- Qift -->
            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="gift-check" name="gift-check" <?= $event['gift'] != "" ? 'checked' : ''; ?>>
                <span class="custom-checkbox"></span>
                <p>Gift:</p>

            </label>
            <div id=gift-div style="display: <?= $event['gift'] != "" ? 'block' : 'none'; ?>;">

                <div id="gift-container"></div>
                <textarea name="gift" id="gift-content" style="display:none;"></textarea>
                <br>
            </div>
            <!-- end gift -->
            <div id=events>
                <h3>Events:</h3>
                <?php foreach ($events as $index => $event) : ?>
                    <div class="eventdiv">
                        <input type="text" name="event_name_<?php echo $index; ?>" value="<?php echo htmlspecialchars($event['name']); ?>" required>
                        <input type="date" name="event_date_<?php echo $index; ?>" value="<?php echo htmlspecialchars($event['date']); ?>" required>
                        <input type="text" class=timepicker name="event_time_<?php echo $index; ?>" value="<?php echo htmlspecialchars($event['time']); ?>" required>
                        <input type="text" name="event_location_name_<?php echo $index; ?>" value="<?php echo htmlspecialchars($event['location_name']); ?>" required>
                        <input type="text" name="event_location_url_<?php echo $index; ?>" value="<?php echo htmlspecialchars($event['location_url']); ?>" required>
                    </div>
                <?php endforeach; ?>
                <button type="button" id=add-event>Add Event</button>
            </div>

            <label for="rsvp_date">RSVP Deadline:</label>
            <input type="date" id=rsvp_date name="rsvp_date" value="<?= htmlspecialchars($event['rsvp_date']); ?>" required>
            <br>


            <strong>Background Photos:</strong>
            <div id="background-preview-container" class="background-preview-container">
                <?php foreach ($background_photos as $photo) : ?>
                    <div class="img-frame saved">
                        <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Background Photo">
                        <button type="button" class="close-btn" data-photo-id="<?php echo $photo['id']; ?>">X</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <label for="file-upload" class="custom-file-upload">
                <span>Select Image</span>
            </label>
            <input type="file" id="file-upload" class=file-upload name="background_photos[]" accept="image/*" multiple><br>

            <strong>Square Photo:</strong>
            <div id="square-photo-preview-container" class="square-photo-preview-container">
                <?php if (!empty($event['square_photo'])) : ?>
                    <div class="img-frame">
                        <img src="<?php echo htmlspecialchars($event['square_photo']); ?>" alt="Square Photo">
                        <button type="button" class="close-btn" data-photo-id="square">X</button>
                    </div>
                <?php endif; ?>
            </div>

            <label for="square-photo-upload" class="custom-file-upload">
                <span>Select Image</span>
            </label>
            <input type="file" class="file-upload" id="square-photo-upload" name="square_photo" accept="image/*"><br>

            <strong for="square-photo">Photo Quote</strong>
            <input type="text" name="ending-quote" value="<?= isset($event['ending_quote']) ? htmlspecialchars($event['ending_quote']) : '' ?>" placeholder="Together Forever">


            <label for="bgmusic-upload" class="custom-file-upload">
                <span>Select Music</span>
            </label>
            <input type="file" class="file-upload" id="bgmusic-upload" name="bgmusic" accept="audio/*"><br>
            <?php if (!empty($event['music'])) : ?>
                <audio id=music controls>
                    <source src="<?php echo htmlspecialchars($event['music']); ?>" type="audio/mpeg">
                </audio>
            <?php endif; ?>
            <br>


            <button type="button" id=submit-form>Save Changes</button>
        </form>

    </div>
    <div id=right>
        <iframe src="../event?id=<?= $_GET['id'] ?>&l=5" seamless></iframe>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        function initializeQuill(containerId, inputId) {
            const FontAttributor = Quill.import('attributors/class/font');
            const Size = Quill.import('attributors/style/size');
            FontAttributor.whitelist = [
                'sofia',
                'slabo',
                'roboto',
                'inconsolata',
                'ubuntu',
                'cursive'
            ];
            Size.whitelist = ['12px', '18px', '24px', '30px', '36px', '42px', '48px', '54px', '60px', '64px'];
            Quill.register(FontAttributor, true);
            Quill.register(Size, true);

            // Initialize Quill
            var quill = new Quill(containerId, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            font: [
                                'sofia',
                                'slabo',
                                'roboto',
                                'inconsolata',
                                'ubuntu',
                                'cursive',
                            ]
                        }], // Font dropdown
                        [{
                            size: ['12px', '18px', '24px', '30px', '36px', '42px', '48px', '54px', '60px', '64px']
                        }],
                        ['bold', 'italic', 'underline'],
                        ['blockquote', 'code-block'],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }],
                        ['clean'] // remove formatting
                    ]
                }
            });

            // Sync content with the hidden input field
            quill.on('text-change', function() {
                var content = quill.root.innerHTML;
                $(inputId).val(content);
            });

            return quill;
        }

        // Initialize editors
        var quoteEditor = initializeQuill('#quote-container', '#quote-content');
        var giftEditor = initializeQuill('#gift-container', '#gift-content');
        var page1Editor = initializeQuill('#page1-container', '#page1-content');
        quoteEditor.root.innerHTML = <?= json_encode($event['quote']) ?>;
        giftEditor.root.innerHTML = <?= json_encode($event['gift']) ?>;
        page1Editor.root.innerHTML = <?= json_encode($event['page1']) ?>;

        $(document).ready(function() {
            $('.timepicker').timepicker({
                'step': 30
            });


            //if music is uploaded, show the audio player or change the source
            $('#bgmusic-upload').on('change', function() {
                var file = this.files[0];
                var audio = document.getElementById('music');
                var url = URL.createObjectURL(file);
                if (audio) {
                    audio.src = url;
                } else {
                    var audio = document.createElement('audio');
                    audio.id = 'music';
                    audio.controls = true;
                    var source = document.createElement('source');
                    source.src = url;
                    source.type = 'audio/mpeg';
                    audio.appendChild(source);
                    this.after(audio);
                }
            });

            // Initialize arrays to store files
            var filesArray = [];
            var squarePhotoFile = null;

            // Generic function to create and append image previews
            function createPreview(container, file, index, isSquare) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    var imgFrame = $('<div>', {
                        class: 'img-frame',
                        'data-index': index
                    });
                    var img = $('<img>', {
                        src: event.target.result,
                        class: isSquare ? 'square-img' : ''
                    });
                    var closeButton = $('<button>', {
                        text: 'X',
                        type: 'button',
                        class: 'close-btn'
                    });

                    // Close button click handler
                    closeButton.click(function() {
                        if (isSquare) {
                            squarePhotoFile = null;
                            container.empty();
                        } else {
                            filesArray.splice(index, 1);
                            updateBackgroundPreviews();
                        }
                    });

                    imgFrame.append(img).append(closeButton);
                    container.append(imgFrame);
                };
                reader.readAsDataURL(file);
            }

            // Update previews for background photos
            function updateBackgroundPreviews() {
                var previewContainer = $('#background-preview-container');
                previewContainer.find('.img-frame:not(.saved)').remove();

                filesArray.forEach((file, index) => createPreview(previewContainer, file, index, false));
            }

            // Background photo input change handler
            $('input[name="background_photos[]"]').on('change', function(e) {
                filesArray = Array.from(e.target.files); // Reset and store files
                updateBackgroundPreviews(); // Render previews
            });

            // Square photo input change handler
            $('input[name="square_photo"]').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    squarePhotoFile = file;
                    var previewContainer = $('#square-photo-preview-container');
                    previewContainer.empty();
                    createPreview(previewContainer, file, 0, true); // Render square photo preview
                }
            });


            // Handle form submission
            $('#submit-form').on('click', function() {
                var formData = new FormData($('#wedding-form')[0]); // Create FormData from the form
                var form = $('#wedding-form')[0];
                var elements = form.elements;

                // Remove files from the input fields in FormData
                $.each(elements, function(index, element) {
                    if (element.type === 'file' && element.name !== 'bgmusic') {
                        formData.delete(element.name); // Delete the file inputs from the FormData
                    }
                });

                // Append files from filesArray to the FormData object
                $.each(filesArray, function(index, file) {
                    formData.append('background_photos[]', file); // Append each background photo to the FormData
                });

                // Append the square photo if it is selected and not removed
                if (squarePhotoFile) {
                    formData.append('square_photo', squarePhotoFile);
                }


                // Submit the form via AJAX
                $.ajax({
                    url: $('#wedding-form').attr('action'), // Use the form's action URL
                    type: 'POST',
                    data: formData,
                    processData: false, // Prevent jQuery from automatically processing the data
                    contentType: false, // Prevent jQuery from setting the content-type
                    success: function(response) {
                        // Handle the server's response here
                        alert('Wedding saved!');
                        // Optionally redirect the user to another page
                        // window.location.href = 'some_page.php';
                    },
                    error: function(xhr, status, error) {
                        // Handle errors here
                        alert('There was an error updating the wedding!');
                    }
                });
            });
            var $quote = $('#quote-div');
            var $gift = $('#gift-div');

            $('#quote-check').on('change', function() {
                if (this.checked) {
                    $quote.show();
                } else {
                    $quote.hide();
                }
            });

            $('#gift-check').on('change', function() {
                if (this.checked) {
                    $gift.show();
                } else {
                    $gift.hide();
                }
            });



            var $page1 = $('#page1-div');
            $('#wedding-check').on('change', function() {
                var $names = $('#couples-name');

                if (this.checked) {
                    $page1.hide();
                    $names.show();
                } else {
                    $page1.show();
                    $names.hide();
                }
            });


            // Add new event fields
            $('#add-event').click(function() {
                const eventCount = $('.eventdiv').length;
                const eventHTML = `
                    <div class="eventdiv">
                        <input type="text" name="event_name_${eventCount}" placeholder="Event Name" required>
                        <input type="date" name="event_date_${eventCount}" required>
                        <input type="text" class="timepicker" name="event_time_${eventCount}" placeholder="Event Time" required>
                        <input type="text" name="event_location_name_${eventCount}" placeholder="Event Location Name" required>
                        <input type="text" name="event_location_url_${eventCount}" placeholder="Event Location URL" required>
                    </div>`;
                $('#events').append(eventHTML);
                // Remove existing timepickers
                $('.timepicker').timepicker('remove');

                // Reinitialize timepickers
                $('.timepicker').timepicker({
                    'step': 30
                });
            });

            // Remove background photo preview and send AJAX request to delete
            $('.close-btn').click(function() {
                const photoId = $(this).data('photo-id');
                alert(photoId + " is getting deleted");
                if (photoId === 'square') {
                    // Remove square photo
                    $('#square-photo-preview-container').empty();
                    $.post('delete_photo.php', {
                        wedding_id: <?php echo $wedding_id; ?>,
                        photo_id: 'square'
                    });
                } else {
                    // Remove background photo
                    $(this).parent().remove();
                    $.post('delete_photo.php', {
                        wedding_id: <?php echo $wedding_id; ?>,
                        photo_id: photoId
                    });
                }
            });
        });
    </script>


</body>

</html>