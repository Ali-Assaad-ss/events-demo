<?php
include '../includes/db.php';

$limit = isset($_GET['l']) ? intval($_GET['l']) : 10;
if ($limit <= 0) {
    die('Invalid');
}


$pageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pageId <= 0) {
    die('Invalid wedding ID');
}

// Fetch wedding details
$stmt = $conn->prepare("SELECT * FROM weddings WHERE id = ?");
$stmt->bind_param("i", $pageId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) {
    echo "404 Not Found";
    exit();
}

// Fetch background photos
$stmt = $conn->prepare("SELECT * FROM wedding_images WHERE wedding_id = ?");
$stmt->bind_param("i", $pageId);
$stmt->execute();
$background_photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch events
$stmt = $conn->prepare("SELECT * FROM events WHERE wedding_id = ?");
$stmt->bind_param("i", $pageId);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<!-- <html lang="en" dir="ltr" prefix="content: http://purl.org/rss/1.0/modules/content/ dc: http://purl.org/dc/terms/ foaf: http://xmlns.com/foaf/0.1/ og: http://ogp.me/ns# rdfs: http://www.w3.org/2000/01/rdf-schema# sioc: http://rdfs.org/sioc/ns# sioct: http://rdfs.org/sioc/types# skos: http://www.w3.org/2004/02/skos/core# xsd: http://www.w3.org/2001/XMLSchema#"> -->

<head>
    <title><?= $event["groom"] ?> <?php if ($event["bride"]) echo "& ".$event["bride"] ?>'s event</title>
    <meta name="format-detection" content="no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&amp;family=Lora:wght@400;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Sofia|Slabo|Roboto|Inconsolata|Ubuntu" rel="stylesheet">
    <link href="css/slick.css" rel="stylesheet">
    <link href="css/slick-theme.css" rel="stylesheet">
    <link href="css/event.css" rel="stylesheet">
    <link href="css/quill.snow.css" rel="stylesheet">




    <script>
        var evAutoPlay = false;
        var evAutoplaySpeed = 1.0E+20;
        var evInfinteLoop = false;
        var VideoBufferPercent = 1;
    </script>

    <script src="js/jquery-1.11.1.js"></script>
    <script src="js/jquery.cookie.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/add-to-calendar-button@2" async="" defer=""></script>

    <script src="js/jquery.countdown.min.js"></script>
    <script src="js/leaves.js"></script>
    <script src="js/event.js"></script>
</head>

<body class="card_ctype tpltype-1 card-lang-en ct_slider">

    <script>
        var isVertical, tplID, isNPvar;
        var npCount = 0;

        isCoverPhoto = false;

        isVertical = false;
        tplID = 1;
        isNPvar = true;
        npCount = 10;
    </script>

    <div id="main" class="tpl-1 mobileOnlyTPL lang-en nav-horizontal ctnt-top" data-language="en">

        <div class="main_wrapper">

            <section id="sec_intro" class="site_section">
                <div class="section_wrapper">
                    <div class="section_content">






                        <div class="HeroSlider_wrapper mobvers">
                            <?php foreach ($background_photos as $photo) : ?>
                                <div class="visItem">
                                    <div class="visItemBG" style="background-image:url('<?= $photo['file_path'] ?>');"></div>
                                </div>
                            <?php endforeach; ?>
                        </div><!-- /  HeroSlider_wrapper - mob -->




                        <div class="HeroSlider">
                            <?php if ($event['type'] == 'wedding') : ?>
                            <div class="introLineTXT_parent">
                                    <div class="introLineTXT curvy" data-introsec="">
                                        <p>
                                            <?= htmlspecialchars($event['groom']) ?><br>
                                            &amp;<br>
                                            <?= htmlspecialchars($event['bride']) ?>
                                        </p>
                                    </div>
                                    <div class="intro_subTitle">
                                        Are getting married
                                    </div>
                                </div>
                                <?php endif; ?>

                            <?php if ($event['type'] == 'event') : ?>
                                <div class="introLineTXT_parent">
                                <div class="introLineTXT" data-introsec="">
                                    <p>
                                        <?= $event['page1'] ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                            <div class="intro_screen"></div>
                        </div> <!-- /  HeroSlider -->


                    </div>


                </div>

            </section><!-- /#sec_intro -->

            <section id="sec_story" class="site_section">
                <div class="section_wrapper">
                    <div class="section_content">
                        <div id="story_slider">

                            <!-- slide -->
                            <div class="story_slide helperSlide">
                                <div class="story_slide_wrapper">

                                </div>

                            </div>

                            <!-- if quote in wedding table is not empry add slider with quote inside -->
                            <?php if ($event['quote']) : ?>
                                <div class="story_slide  generic sld1  " style="background-image:url()">

                                    <div class="slide_main_container noShade">
                                        <div class="story_slide_wrapper">
                                            <h1 class="title visible-title"><span class="hdtitle Title_Disp">&nbsp;</span><span class="subtitle"></span></h1>
                                            <?= $event['quote'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <?php foreach ($events as $event) : ?>
                                <div class="story_slide  location locBtnDisp sld2  " style="background-image:url()">

                                    <div class="slide_main_container noShade">
                                        <div class="story_slide_wrapper">
                                            <h1 class="title visible-title"><span class="hdtitle Title_Disp"><?= $event['name'] ?>
                                                </span></h1>


                                            <div class="event_details">
                                                <div class="info-row date">
                                                    <div class="col icon"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13 0C5.82029 0 0 5.82034 0 13C0 20.1796 5.82029 26 13 26C20.1797 26 26 20.1796 26 13C26 5.82034 20.1797 0 13 0ZM13 4.81481C13.5318 4.81481 13.963 5.24593 13.963 5.77778V12.4433L18.8981 15.3021C19.3587 15.568 19.5252 16.1505 19.2593 16.6111C18.9933 17.0717 18.3958 17.2231 17.9352 16.9572C16.1322 15.9153 14.314 14.8643 12.5185 13.8276C12.2311 13.6609 12.037 13.3561 12.037 13V5.77778C12.037 5.24593 12.4682 4.81481 13 4.81481Z" fill="white"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="col info">
                                                        <?php $date = new DateTime($event['date']); ?>
                                                        <span class="data-line-1"><?= $date->format('F j, Y') ?> </span>
                                                        <?php $time = new DateTime($event['time']); ?>
                                                        <span class="data-line-2"><?= $time->format('g:i A') ?></span>
                                                    </div>
                                                </div>
                                                <div class="info-row loc">
                                                    <div class="col icon"><a href="<?= $event["location_url"] ?>" target="_blank"><svg x="0px" y="0px" width="96.507px" height="154.835px" viewBox="0 0 96.507 154.835" enable-background="new 0 0 96.507 154.835" xml:space="preserve">
                                                                <g>
                                                                    <g transform="translate(-5286.000000, -3586.000000)">
                                                                        <path fill="#ffffff" d="M5334.254,3655.997c-11.851,0-21.458-9.607-21.458-21.458c0-11.851,9.607-21.458,21.458-21.458
          s21.458,9.607,21.458,21.458C5355.711,3646.39,5346.105,3655.997,5334.254,3655.997 M5382.486,3632.985
          c-0.028-0.775-0.092-1.515-0.183-2.229c-1.153-24.907-22.25-44.756-48.113-44.756c-26.603,0-48.169,20.999-48.169,46.904
          c0,0.02,0.001,0.04,0.001,0.061c0,0.007-0.001,0.013-0.001,0.02c-1.184,32.324,48.169,107.85,48.169,107.85l0.064-0.761
          l0.063,0.761C5334.317,3740.834,5383.67,3665.309,5382.486,3632.985"></path>
                                                                    </g>
                                                                </g>
                                                            </svg></a></div>
                                                    <div class="col info">
                                                        <span class="data-line-1"><?= $event["location_name"] ?></span>
                                                    </div>
                                                    <div class="col mapBtn mapBtn_1">
                                                        <a href="<?= $event["location_url"] ?>
                                                    " target="_blank"><span>Location Map</span></a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>






                            <?php if ($event['gift']) : ?>
                                <div class="story_slide  generic sld3  " style="background-image:url()">
                                    <div style="height:auto;" class="slide_main_container noShade">
                                        <div class="story_slide_wrapper">
                                            <?= $event['gift'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <!-- RSVP -->

                            <!-- slide -->
                            <div class="story_slide rsvp  sld4">
                                <div class="story_slide_wrapper">
                                    <h1 class="title">Be Our Guest</h1>


                                    <div class="slide-content">
                                        <div class="form-intro-wrapper">

                                            <?php $rsvp_date = new DateTime($event['rsvp_date']); ?>
                                            Please reply before <?= $rsvp_date->format('F j, Y') ?>

                                        </div>

                                        <div class="form-wrapper">

                                            <form id="rsvp-event" name="rsvp_form">

                                                <div class="form-field fld-guests">
                                                    <label for="guests">Are You Attending?</label>
                                                    <select id="guests" name="guests">
                                                        <option value="">- select -</option>
                                                        <option value="1">Yes Attending</option>
                                                        <option value="na">Not Attending</option>
                                                    </select>
                                                </div>

                                                <div class="form-field fld-npersons" style=display:hidden;>
                                                    <label for="npersons">Number of Attendees</label>
                                                    <select id="npersons" name="npersons">
                                                        <option value="">- select -</option>
                                                        <?php foreach (range(1, $limit) as $i) : ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <br>
                                                <div id="rsvp_div">
                                                </div>
                                                <input type="text" name=wedding_id value=<?= $pageId ?> hidden>
                                                <button id="submit_rsvp" type="submit">

                                                    RSVP<div class="rsvp-preloader">
                                                        <div class="rsvp-preloader-wrapper">

                                                            <div class="sk-cube-grid">
                                                                <div class="sk-cube sk-cube1"></div>
                                                                <div class="sk-cube sk-cube2"></div>
                                                                <div class="sk-cube sk-cube3"></div>
                                                                <div class="sk-cube sk-cube4"></div>
                                                                <div class="sk-cube sk-cube5"></div>
                                                                <div class="sk-cube sk-cube6"></div>
                                                                <div class="sk-cube sk-cube7"></div>
                                                                <div class="sk-cube sk-cube8"></div>
                                                                <div class="sk-cube sk-cube9"></div>
                                                                <div class="loading_lbl">Loading..</div>
                                                            </div>
                                                            <span class="waitTXT"> Please Wait..</span>
                                                        </div>
                                                    </div> </button>



                                                <div class="form-lock-screen"></div>
                                            </form>


                                            <div class="ajax_success_message">
                                                <div class="ajax_success_message_wrapper">
                                                    <p>Thank you for your confirmation!</p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>



                            <!-- end slide -->
                            <? if ($event['square_photo']) : ?>

                                <div class="story_slide endFrame sld4">
                                    <div class="story_slide_wrapper">

                                        <div class="slide-content">
                                            <div class="photo-frame">
                                                <div class="photo-frame-wrapper">

                                                    <img src=<?= $event['square_photo'] ?> class="photo-frame-img">

                                                </div>
                                                <span class="photo-title"><?= $event["ending_quote"] ?></span>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            <? endif; ?>


                            <!-- sponsor slide -->



                        </div><!-- / #story_slider -->

                    </div>

                </div>



            </section>
            <div class="progress_bar">
                <div class="progress_state"></div>
            </div>


            <div class="prompt">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.2 2.5A4.2 4.2 0 005 7.4c.2.9.7 1.8 1.3 2.4a.5.5 0 10.6-.7c-.4-.4-.8-1.2-1-1.9A3.2 3.2 0 1112.2 6c.2.7 0 1.5-.2 2.1a.5.5 0 10.9.4c.3-.8.4-1.8.2-2.7a4.2 4.2 0 00-4.9-3.3zM9 5.7c-.8.1-1.3.9-1.1 1.6l1.6 8.2L8 14c-1-1.2-1.7-1-2.2-.5-.6.6-.8 1-.1 2.1 1.5 2.4 3.7 5 5.8 6.7l6.2-1.2c1.8-2 2-10-1-9.7-.3 0-.7.1-.9.4-.1-1-1-1-1.3-1-.4.1-.7.4-.8.9-.2-1-1-1-1.4-1-.4.2-.9.5-1 1l-.9-4.8c-.2-.8-.9-1.3-1.6-1.1z" fill="white" fill-rule="nonzero"></path>
                </svg>
                <span class="prompt_label">Swipe to continue</span>
            </div>





            <div id="preloaderCont">

                <div class="sk-cube-grid">
                    <div class="sk-cube sk-cube1"></div>
                    <div class="sk-cube sk-cube2"></div>
                    <div class="sk-cube sk-cube3"></div>
                    <div class="sk-cube sk-cube4"></div>
                    <div class="sk-cube sk-cube5"></div>
                    <div class="sk-cube sk-cube6"></div>
                    <div class="sk-cube sk-cube7"></div>
                    <div class="sk-cube sk-cube8"></div>
                    <div class="sk-cube sk-cube9"></div>
                    <div class="loading_lbl">Loading..</div>
                </div>
            </div>

            <?php if ($event["music"]) : ?>
                <audio id="bgmusic" autoplay="" loop="">
                    <source src="<?= $event["music"] ?>" type="audio/mpeg">
                </audio>

                <button id="music_player_BTN">
                    <svg fill="#000000" height="800px" width="800px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 58 58" xml:space="preserve">
                        <g>
                            <g>
                                <path d="M29,0C13.01,0,0,13.009,0,29s13.01,29,29,29s29-13.009,29-29S44.99,0,29,0z M29,56C14.112,56,2,43.888,2,29S14.112,2,29,2
			s27,12.112,27,27S43.888,56,29,56z" />
                                <path d="M43.994,19.202c0.003-2.973,0.006-5.781-0.044-8.552c-0.016-0.811-0.519-1.325-1.313-1.343
			c-0.478-0.015-0.953,0.02-1.43,0.054l-0.243,0.017c-2.592,0.17-5.127,0.353-7.657,0.723c-4.572,0.668-8.637,1.727-12.428,3.24
			c-0.478,0.191-0.773,0.577-0.833,1.084c-0.042,0.354-0.044,0.708-0.044,1.087C20,22.293,19.998,29.074,20.004,35.878
			c-2.247-0.426-4.352,0.055-6.277,1.433c-1.177,0.842-1.982,1.909-2.394,3.169c-0.649,1.978-0.356,3.749,0.872,5.264
			c0.604,0.745,1.392,1.126,2.082,1.424c1.102,0.476,2.193,0.713,3.27,0.713c1.618,0,3.203-0.536,4.738-1.606
			c1.705-1.188,2.632-2.906,2.681-4.969c0.023-0.987,0.024-1.974,0.025-2.961l0.002-0.848c0.005-1.553,0-3.106-0.004-4.66
			c-0.009-2.822-0.018-5.74,0.036-8.614c0-0.017,0.001-0.034,0.001-0.05c0.016-0.004,0.032-0.009,0.05-0.014
			c2.964-0.864,5.941-1.542,8.849-2.014c1.939-0.315,3.564-0.504,5.052-0.587c-0.006,3.426-0.007,6.851,0.001,10.276
			c-0.346-0.07-0.715-0.121-1.102-0.132c-2.609-0.077-4.718,0.8-6.329,2.6c-1.217,1.36-1.729,2.967-1.479,4.647
			c0.304,2.059,1.541,3.525,3.676,4.356c2.231,0.869,4.513,0.66,6.783-0.624v0C42.803,41.398,44,39.355,44,36.774V25.091
			C43.99,22.996,43.992,21.052,43.994,19.202z M42,36.774c0,1.871-0.801,3.233-2.448,4.166c-1.746,0.986-3.404,1.152-5.072,0.501
			c-1.457-0.567-2.227-1.453-2.424-2.786c-0.163-1.104,0.161-2.092,0.991-3.021c1.217-1.36,2.772-1.995,4.78-1.935
			c0.544,0.016,1.088,0.158,1.548,0.294c0.167,0.049,0.676,0.2,1.143-0.147c0.216-0.161,0.473-0.475,0.473-1.056
			c-0.012-3.982-0.011-7.964-0.002-11.945c0.002-0.411-0.123-0.74-0.371-0.981c-0.247-0.239-0.585-0.343-0.981-0.339
			c-1.766,0.059-3.679,0.264-6.021,0.644c-2.988,0.485-6.046,1.181-9.097,2.071c-1.169,0.34-1.46,0.722-1.482,1.954
			c-0.054,2.886-0.045,5.815-0.036,8.647c0.004,1.549,0.009,3.098,0.004,4.647l-0.002,0.852c-0.001,0.972-0.002,1.944-0.025,2.917
			c-0.033,1.42-0.646,2.556-1.823,3.376c-1.979,1.378-3.967,1.607-6.073,0.697c-0.618-0.267-1.032-0.491-1.32-0.848
			c-0.795-0.98-0.962-2.054-0.526-3.382c0.281-0.86,0.824-1.568,1.658-2.165c1.128-0.807,2.304-1.208,3.557-1.208
			c0.628,0,1.274,0.1,1.944,0.3c0.169,0.051,0.687,0.204,1.149-0.141c0.464-0.346,0.464-0.887,0.464-1.066
			C21.998,29.72,22,22.616,22.002,15.488c0-0.148-0.001-0.294,0.004-0.442c3.533-1.373,7.33-2.345,11.59-2.967
			c2.457-0.359,4.949-0.539,7.5-0.706l0.25-0.017c0.205-0.014,0.41-0.029,0.615-0.039c0.038,2.549,0.036,5.146,0.033,7.883
			c-0.002,1.853-0.004,3.801,0.006,5.896C42,25.096,42,36.774,42,36.774z" />
                            </g>
                        </g>
                    </svg> </button>



                <div id="ios_audio_lockscreen" style="display:none;">
                    <div class="btn_wrapper">
                        <button id="audioLockScreen">Tap to start</button>
                        <!-- <button id="audioLockScreen">Start</button> -->
                        <!-- <span class="iosBTNinfo">Tap to start</span> -->
                    </div>

                </div>
            <?php endif; ?>

        </div><!-- /main_wrapper -->
        <div class="deskBG_mobonly" style="background-color:#000000;">
            <div class="deskBG_mobonly_wrapper" style="background-image:url('images/default_desk_1.jpg');"></div>
        </div>


    </div><!-- #main -->


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&display=swap');

        body,
        html {
            font-family: 'Lora', serif;
        }

        .story_slide.generic.sld0 .slide-content {
            font-size: 18px;
        }

        .story_slide.generic .slide-content {
            font-size: 16px;
        }

        .sld0 .slideBody h3 {
            font-size: 43px;
            line-height: 52px;
            font-family: 'Great Vibes', cursive;
            font-weight: normal;
            margin: 43px 0 25px;
        }

        .sld0 .slideBody h5 {
            font-size: 34px;
            line-height: 40px;
            font-family: 'Great Vibes', cursive;
            font-weight: normal;
            margin: 16px 0;
        }

        .story_slide h1.title {
            font-size: 40px;
            font-family: 'Great Vibes', cursive;
            font-weight: normal;
        }

        .story_slide h1.title span.subtitle {
            font-family: 'Ubuntu', sans-serif, Verdana;
        }

        .story_slide h1.title span.subtitle font {
            display: block;
            font-style: italic;
            font-size: 12px;
            padding-top: 5px;
            opacity: 0.7;
        }

        .recptTm {
            padding-top: 8px;
            font-style: italic;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#guests').change(function() {
                if ($(this).val() == '1') {
                    $('.fld-npersons').show();
                } else {
                    $('.fld-npersons').hide();
                    // show one name input
                    $('#rsvp_div').html(`<div class="form-field fld-name"><label for="full_name">Name </label><input class=full_name placeholder="Full Name" id="full_name" type="text" name="full_name"></div>`);
                }
            });
        });
        // add inputs based on the number of guests
        $(document).ready(function() {

            $('#npersons').change(function() {
                var np = $(this).val();
                np = parseInt(np);
                var html = '';
                for (var i = 1; i < np + 1; i++) {
                    html += `<div class="form-field fld-name"><label for="full_name${i}">Person ${i} </label><input class=full_name placeholder="Full Name" id="full_name${i}" type="text" name="full_name${i}"></div>`;
                }
                $('#rsvp_div').html(html);
            });
        });
        //sliderspeed update 1
        $(document).ready(function() {

            evBGSliderSpeed = 6;

            $(".HeroSlider_wrapper").slick({
                'arrows': false,
                'fade': true,
                'autoplay': true,
                'autoplaySpeed': 6000,
                'pauseOnFocus': false,
                'pauseOnHover': false,
                'touchMove': false,
                'draggable': false

            });

        });



        $('#rsvp-event').submit(function(event) {
            event.preventDefault(); // Prevent the form from submitting normally

            // Collect all input values into an object
            var formData = {};
            formData['wedding_id'] = $('input[name="wedding_id"]').val();
            formData['npersons'] = $('#npersons').val();
            formData['guests'] = $('#guests').val();
            $('.full_name').each(function() {
                var name = $(this).attr('name');
                var value = $(this).val();
                formData[name] = value;
            });

            // Send the collected data via AJAX
            $.ajax({
                type: 'POST',
                url: 'rsvp.php', // Update with your PHP file that handles the request
                data: formData,
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        RSVP_success_msg(data.msg);
                        $('form#rsvp-event').removeClass('lock-form');
                        //$.cookie('RSVPstate', '1');

                        if (evAutoPlay) {
                            storySlider.slick('slickPlay');
                        }
                    } else {
                        //alert('Something went wrong, please try again');
                        RSVP_success_msg(data.msg);
                        $('form#rsvp-event').removeClass('lock-form');
                        if (evAutoPlay) {
                            storySlider.slick('slickPlay');
                        }
                    }
                },
                error: function() {
                    alert('Error: Something went wrong, please try again');
                    $('form#rsvp-event').removeClass('lock-form');
                }
            });
        });
    </script>
</body>

</html>