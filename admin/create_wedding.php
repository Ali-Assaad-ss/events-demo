<?php
include '../includes/db.php';

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




    // Handle file uploads
    $upload_dir = '../uploads/images/'; // Directory to store the uploaded files
    $uploaded_files = [];

    // Process background photos (multiple)
    if (isset($_FILES['background_photos'])) {
        foreach ($_FILES['background_photos']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['background_photos']['name'][$key]);
            $file_tmp = $_FILES['background_photos']['tmp_name'][$key];
            $file_type = $_FILES['background_photos']['type'][$key];
            $file_size = $_FILES['background_photos']['size'][$key];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Generate a unique name for the file to avoid conflicts
            $new_file_name = uniqid('', true) . '.' . $file_ext;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $uploaded_files[] = $upload_dir . $new_file_name; // Store the file path for later use
            }
        }
    }

    // Process square photo (single file)
    $square_dir = null;
    if (isset($_FILES['square_photo']) && $_FILES['square_photo']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['square_photo']['name']);
        $file_tmp = $_FILES['square_photo']['tmp_name'];
        $file_type = $_FILES['square_photo']['type'];
        $file_size = $_FILES['square_photo']['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Generate a unique name for the file
        $new_file_name = uniqid('square_', true) . '.' . $file_ext;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $square_dir = $upload_dir . $new_file_name; // Store the file path for later use
        }
    }
    // Process music file
    $music_dir = null;
    if (isset($_FILES['music-upload']) && $_FILES['music-upload']['error'] === UPLOAD_ERR_OK) {
        $music_dir = '../uploads/music/';
        $file_name = basename($_FILES['music-upload']['name']);
        $file_tmp = $_FILES['music-upload']['tmp_name'];
        $file_type = $_FILES['music-upload']['type'];
        $file_size = $_FILES['music-upload']['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Generate a unique name for the file
        $new_file_name = uniqid('music_', true) . '.' . $file_ext;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($file_tmp, $music_dir . $new_file_name)) {
            $music_dir = $music_dir . $new_file_name; // Store the file path for later use
        }
    }
    $uuid = uniqid();

    // Insert the wedding data
    $stmt = $conn->prepare("INSERT INTO weddings (groom, bride, quote, gift, rsvp_date, uuid, type, page1, ending_quote) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $groom, $bride, $quote, $gift, $rsvp_date, $uuid, $type, $page1, $ending_quote);
    $stmt->execute();
    $wedding_id = $stmt->insert_id;

    if ($wedding_id) {
        // Insert the uploaded files' paths into the database if needed
        if (!empty($uploaded_files)) {
            foreach ($uploaded_files as $file_path) {
                $stmt = $conn->prepare("INSERT INTO wedding_images (wedding_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $wedding_id, $file_path);
                $stmt->execute();
            }
        }

        // Insert the square photo path if available
        if ($square_dir) {
            $stmt = $conn->prepare("UPDATE weddings SET square_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $square_dir, $wedding_id);
            $stmt->execute();
        }

        /// Insert the music path if available
        if ($music_dir) {
            $stmt = $conn->prepare("UPDATE weddings SET music = ? WHERE id = ?");
            $stmt->bind_param("si", $music_dir, $wedding_id);
            $stmt->execute();
        }

        $events = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'event_name') !== false) {
                $event = [
                    'name' => $value,
                    'date' => $_POST[str_replace('event_name', 'event_date', $key)],
                    'time' => $_POST[str_replace('event_name', 'event_time', $key)],
                    'location_name' => $_POST[str_replace('event_name', 'event_location_name', $key)],
                    'location_url' => $_POST[str_replace('event_name', 'event_location_url', $key)],
                ];
                $events[] = $event;
            }
        }
        foreach ($events as $event) {
            $stmt = $conn->prepare("INSERT INTO events (wedding_id, name, date, time, location_name, location_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $wedding_id, $event['name'], $event['date'], $event['time'], $event['location_name'], $event['location_url']);
            $stmt->execute();
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create New Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Sofia|Slabo|Roboto|Inconsolata|Ubuntu" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&amp;family=Lora:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="create_wedding.css">
    <link rel="stylesheet" href="create_wedding2.css">

</head>

<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: white; color: black; width:100%;height:60px;position:fixed;top:0;z-index:100;border-bottom: 1px solid #8b7e6f;border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;">
        <!-- <a href="weddings.html" style="font-size: 20px; text-decoration: none; color: black; margin-inline:20px;">Weddings</a> -->
        <img style=" margin-inline:20px; cursor:pointer" src="events-Logo.png" width="200px" height=130px onclick="window.location.href='view_weddings.php'">
        <button onclick="window.location.href='logout.php'" style="padding:10px 10px; background-color:#baaf11ab; color: white; border: none; cursor: pointer; width:120px; margin-inline:20px;">Logout</button>
    </header>
    <div id=main>
        <form action="" method="POST" enctype="multipart/form-data" id=wedding-form>
            <h1>Create New Event</h1>

            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="wedding-check" name="wedding-check" checked=true>
                <span class="custom-checkbox"></span>
                <p>Wedding</p>
            </label>

            <div id="couples-name">
                <label>Couple Names:</label><br>
                <div class=couples-names>
                    <input type="text" name="groom" placeHolder="Groom" required><br>
                    <input type="text" name="bride" placeholder="Bride" required><br>
                </div>

            </div>
            <!-- page 1 -->

            <div id=page1-div>
                <label>Event Name</label><br>

                <input type="text" name=event-name placeHolder="event's name">

                <label>First Page:</label><br>
                <div id="page1-container"></div>
                <textarea name="page1" id="page1-content" style="display:none;"></textarea>
                <br>
            </div>

            <!-- quote -->
            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="quote-check" name="quote-check" checked=true>
                <span class="custom-checkbox"></span>
                <p>Quote:</p>

            </label>
            <div id=quote-div>

                <div id="quote-container"></div>
                <textarea name="quote" id="quote-content" style="display:none;"></textarea>
                <br>
            </div>
            <!-- end quote -->

            <!-- Qift -->
            <label style=display:flex;align-items:center;gap:5px;align-self:flex-start>
                <input type="checkbox" id="gift-check" name="gift-check" checked=true>
                <span class="custom-checkbox"></span>
                <p>Gift:</p>

            </label>
            <div id=gift-div>

                <div id="gift-container"></div>
                <textarea name="gift" id="gift-content" style="display:none;"></textarea>
                <br>
            </div>
            <!-- end gift -->


            <div id=events>
                <div style=display:flex;gap:50px;margin-bottom:10px;>
                    <select name="eventsType" id="eventsType">
                        <option value="Wedding Ceremony">Wedding Ceremony</option>
                        <option value="Wedding party">Wedding party</option>
                        <option value="Wedding Dinner">Wedding Dinner</option>
                    </select>
                    <button type="button" id=add-event>Add Event</button>
                </div>
            </div>

            <label for="rsvp_date">RSVP Deadline:</label>
            <input type="date" id=rsvp_date name="rsvp_date" required>
            <br>

            <!-- Allow multiple background photo uploads -->
            <strong for="background-photo">Background Photos (You can upload multiple):</strong>
            <label for="file-upload" class="custom-file-upload">
                <span>Select Image</span>
            </label>
            <input type="file" id="file-upload" class=file-upload name="background_photos[]" accept="image/*" multiple><br>

            <!-- Display selected background photos in a small frame -->
            <div id="background-preview-container" class="background-preview-container">
                <!-- Thumbnails will appear here -->
            </div>

            <!-- Add square photo upload -->
            <strong for="square-photo">Square Photo (Optional):</strong>
            <label for="file-upload2" class="custom-file-upload">
                <span>Select Image</span>
            </label>
            <input type="file" class="file-upload" id="file-upload2" name="square_photo" accept="image/*"><br>

            <!-- Add square photo upload -->
            <strong for="square-photo">Photo Quote</strong>
            <input type="text" name="ending-quote" placeholder="Together Forever">

            <!-- Display the preview of the selected square photo -->
            <div id="square-photo-preview-container" class="square-photo-preview-container">
                <!-- Square photo preview will appear here -->
            </div>
            <label for="music-upload" class="custom-file-upload">
                <span>Select Music</span>
            </label>
            <input type="file" class="file-upload" id="music-upload" name="music-upload" accept="audio/*"><br>

            <button type="button" id="submit-form">Create event</button>
        </form>
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
        var giftEditor = initializeQuill('#page1-container', '#page1-content');

        $(document).ready(function() {
            // Initialize an array to store the file objects
            var filesArray = [];
            var squarePhotoFile = null; // Store the square photo file

            // Handle background photo file input and preview the images
            $('input[name="background_photos[]"]').on('change', function(e) {
                var files = e.target.files;
                var previewContainer = $('#background-preview-container');
                previewContainer.empty(); // Clear any previous previews
                filesArray = []; // Clear the previous array

                // Loop through each file and add it to the filesArray
                $.each(files, function(index, file) {
                    var reader = new FileReader();

                    reader.onload = function(event) {
                        // Create a small frame for each image
                        var imgFrame = $('<div>', {
                            class: 'img-frame',
                            'data-index': filesArray.length // Add an index to identify the image in the array
                        });

                        // Create image element
                        var img = $('<img>', {
                            src: event.target.result
                        });

                        // Create close (X) button to remove the image
                        var closeButton = $('<button>', {
                            text: 'X',
                            type: 'button',
                            class: 'close-btn',
                            click: function() {
                                // Get the index of the image to remove
                                var index = $(imgFrame).data('index');
                                filesArray.splice(index, 1); // Remove the file from the array

                                // Re-render the previews based on the updated array
                                updatePreviews();
                            }
                        });

                        // Add the file to the filesArray
                        filesArray.push(file);

                        // Append the image and close button to the frame
                        imgFrame.append(img).append(closeButton);
                        previewContainer.append(imgFrame); // Append the frame to the preview container
                    };

                    reader.readAsDataURL(file); // Read the image file as data URL
                });
            });

            // Function to update the background previews
            function updatePreviews() {
                var previewContainer = $('#background-preview-container');
                previewContainer.empty(); // Clear any previous previews

                // Loop through the filesArray and re-render the previews
                $.each(filesArray, function(index, file) {
                    var reader = new FileReader();

                    reader.onload = function(event) {
                        var imgFrame = $('<div>', {
                            class: 'img-frame',
                            'data-index': index // Set the data-index for the file
                        });

                        var img = $('<img>', {
                            src: event.target.result
                        });

                        var closeButton = $('<button>', {
                            text: 'X',
                            type: 'button',
                            class: 'close-btn',
                            click: function() {
                                filesArray.splice(index, 1); // Remove the file from the array
                                updatePreviews(); // Re-render the previews
                            }
                        });

                        imgFrame.append(img).append(closeButton);
                        previewContainer.append(imgFrame);
                    };

                    reader.readAsDataURL(file);
                });
            }

            $('#music-upload').on('change', function() {
                var fileName = this.files[0]?.name || 'No file selected';
                $(this).prev('.custom-file-upload').find('span').text(fileName);
            });

            // Handle square photo preview and removal
            $('input[name="square_photo"]').on('change', function(e) {
                var file = e.target.files[0];
                var previewContainer = $('#square-photo-preview-container');
                previewContainer.empty(); // Clear any previous previews

                if (file) {
                    var reader = new FileReader();

                    reader.onload = function(event) {
                        // Create a frame for the square photo preview
                        var imgFrame = $('<div>', {
                            class: 'img-frame',
                        });

                        // Create image element
                        var img = $('<img>', {
                            src: event.target.result,
                            class: 'square-img'
                        });

                        // Create close (X) button to remove the image
                        var closeButton = $('<button>', {
                            text: 'X',
                            type: 'button',
                            class: 'close-btn',
                            click: function() {
                                squarePhotoFile = null; // Remove the file from the variable
                                previewContainer.empty(); // Clear the preview
                            }
                        });

                        // Store the selected file in squarePhotoFile
                        squarePhotoFile = file;

                        // Append the image and close button to the frame
                        imgFrame.append(img).append(closeButton);
                        previewContainer.append(imgFrame); // Append the frame to the preview container
                    };

                    reader.readAsDataURL(file); // Read the square photo file as data URL
                }
            });

            // Handle form submission
            $('#submit-form').on('click', function() {
                var formData = new FormData($('#wedding-form')[0]); // Create FormData from the form
                var form = $('#wedding-form')[0];
                var elements = form.elements;

                // Remove files from the input fields in FormData
                $.each(elements, function(index, element) {
                    if (element.type === 'file' && element.name !== "music-upload") {
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
                        alert('Wedding created successfully!');
                        // Optionally redirect the user to another page
                        // window.location.href = 'some_page.php';
                    },
                    error: function(xhr, status, error) {
                        // Handle errors here
                        alert('There was an error uploading the wedding!');
                    }
                });
            });
        });



        $('#quote-check').on('change', function() {
            var $quote = $('#quote-div');
            if (this.checked) {
                $quote.show();
            } else {
                $quote.hide();
            }
        });

        $('#gift-check').on('change', function() {
            var $quote = $('#gift-div');
            if (this.checked) {
                $quote.show();
            } else {
                $quote.hide();
            }
        });
        var $page1 = $('#page1-div');
        $page1.hide();
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

        $('#add-event').on('click', function() {
            var $events = $('#events');
            var eventType = $('#eventsType').val();
            // add a div wthi class name eventdiv
            var $div = $('<div>', {
                class: 'eventdiv'
            });
            $div.html(`
        <input type="text" name="event_name" id="event_name" placeholder="Event Name" required>
        <input type="date" name="event_date" required>
        <input type="text" name="event_time" id="timepicker" required>
        <input type="text" name="event_location_name" placeholder="Event Location Name" required>
        <input type="text" name="event_location_url" placeholder="Event Location URL" required>
    `);
            $div.find('#timepicker').timepicker({
                'step': 30 // Set the interval to 10 minutes
            });
            $events.append($div);
            $div.find('#event_name').val(eventType);
        });
    </script>
</body>

</html>