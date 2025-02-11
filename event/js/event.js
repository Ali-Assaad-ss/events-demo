$(document).ready(function (e) {
  appHeight(tplID);

  $(".HeroSlider_wrapper").on("init", function (event, slick, direction) {
    setTimeout(function () {
      $(".HeroSlider_wrapper").addClass("ini_zoom");
    }, 300);
  });

  var VerticalNav = false;
  var Verticalswipe = false;

  if ($(window).width() > 1024 || isVertical) {
    VerticalNav = true;
    Verticalswipe = true;
  }
  $(".ct_slider .story_slide.spacerSlide").remove();
  var storySlider = $(".ct_slider #story_slider");
  storySlider.slick({
    arrows: false,
    dots: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    infinite: false,
    pauseOnFocus: false,
    pauseOnHover: false,
    vertical: VerticalNav,
    verticalSwiping: Verticalswipe,
    autoplay: false,
  });

  //Advanced Slick CustomAutoPlay
  if (evAutoplaySpeed && evAutoplaySpeed > 0) {
    if ($("#audioLockScreen").length === 0) {
      customAutoPlay(evAutoplaySpeed);
    }
  }

  if (evAutoPlay && $("#ios_audio_lockscreen").length) {
    storySlider.slick("slickPause");
  }

  storySlider.on("wheel", function (e) {
    e.preventDefault();

    if (e.originalEvent.deltaY < 0) {
      $(this).slick("slickPrev");
    } else {
      $(this).slick("slickNext");
    }
  });

  slidesCount = $("#story_slider .story_slide").length - 1;

  var audio_flag = 0;
  var bgAudio = document.getElementById("bgmusic");

  $(".ct_slider #story_slider").on(
    "beforeChange",
    function (e, slick, currentSlide, nextSlide) {
      //console.log(nextSlide);
      if (nextSlide != 0) {
        $(".introLineTXT_parent").addClass("hideIntroLine");
        $(".intro_screen").addClass("dim");
        if ($("#music_player_BTN").length > 0) {
          $("#music_player_BTN").addClass("showAudioBtn");
        }
        if ($("#bgmusic").length > 0) {
          if (audio_flag == 0) {
            bgAudio.play();
          }
          audio_flag++;
        }
      } else {
        $(".introLineIMG, .introLineTXT_parent").removeClass("hideIntroLine");
        $(".intro_screen").removeClass("dim");
        if ($("#music_player_BTN").length > 0) {
          $("#music_player_BTN").removeClass("showAudioBtn");
        }
      }
      if (nextSlide == slidesCount) {
        setTimeout(function () {
          $(".ct_slider #sec_story").addClass("showPoweredBy");
        }, 300);
      } else {
        $(".ct_slider #sec_story").removeClass("showPoweredBy");
      }

      var sn_el = slick.$slides.get(nextSlide);
      if ($(sn_el).hasClass("IMGslide")) {
        $(sn_el).addClass("animIMG");
      }
      setTimeout(function () {
        $(sn_el).siblings().removeClass("animIMG");
      }, 300);

      var progress = (nextSlide / slidesCount) * 100;
      $(".progress_bar .progress_state").css("width", progress + "%");
    }
  );

  $("form#rsvp-event input, form#rsvp-event select").on("focus", function () {
    if (evAutoPlay) {
      storySlider.slick("slickPause");
    }
  });

  var isAttending = false;
  $(".form-field select#guests").on("change", function () {
    if (isNPvar) {
      var np_selection = $(this).val();

      if (np_selection == 1) {
        $(".form-field.fld-npersons").fadeIn();
        isAttending = true;
        $(this).addClass("selected");
      } else {
        $(".form-field.fld-npersons").fadeOut();
        isAttending = false;
        $("input#npersons").val("");
        $(this).removeClass("selected");
      }
    }
  });

  $("#music_player_BTN").on("click", function () {
    togglePause();
  });

  if (IsMobile() && $("#ios_audio_lockscreen").length > 0) {
    $("#ios_audio_lockscreen").css("display", "block");
    $(".main_wrapper").addClass("iosAudio");

    $(".ct_article #sec_story").css("opacity", 0);
  }

  var GuestName_fld_val = "";
  //start card
  $("#ios_audio_lockscreen").on("click", function () {
    if ($("body").hasClass("ct_article")) {
      $("html, body").scrollTop(0);

      $(".ct_article #sec_story").css("opacity", 1);

      RunSwipeUpHelper();
    }

    var myAudio_start = document.getElementById("bgmusic");
    if (myAudio_start) {
      myAudio_start.play();
    }
    Fall.init({
      image: "leaf.png", // Custom image for falling leaves
      density: 20, // Number of leaves
    });

    $(this).fadeOut(300);
    setTimeout(function () {
      $(".main_wrapper").removeClass("iosAudio");
      $(".story_slide.helperSlide").addClass("hlpIndicShow");
    }, 400);

    if (evAutoPlay) {
      customAutoPlay(evAutoplaySpeed);
    }

    GuestName_fld_val = $(".form-wrapper .guestNames_dyn_wrapper").text();
    $("form .form-field #full_name").val(GuestName_fld_val);

    $(".HeroSlider_wrapper").slick("slickPlay");

    //TriggerPrompt();

    if (!isCoverPhoto) {
      if (evtCount > evBGSliderSpeed) {
        console.log("jump to next slide");
        $(".HeroSlider_wrapper").slick("slickNext");
      } else {
        console.log("do nothing");
      }
      clearInterval(evTimer);
    }

    if ($(".ct_article #music_player_BTN").length > 0) {
      $(".ct_article #music_player_BTN").addClass("showAudioBtn");
    }

    PlayBgVido();
    BufferVideo(VideoBufferPercent);

    RunPrompter();
  });

  $("form .form-field #message").on("input blur", function () {
    var maxLength = 120;
    var currentLength = $(this).val().length;

    $("#msg_charCount #c_current").text(currentLength);
    if (currentLength > 120) {
      $("#msg_charCount #c_current").text("120");
    }

    if (currentLength > maxLength) {
      $(this).val($(this).val().slice(0, maxLength));
    }
  });

  /** helper animation display script **/
  $(".prompt").fadeOut();

  /* end helper animation script */

  RunSwipeUpHelper();

  $("video#vidbgcomp").addClass("hideVideo");
}); //end ready function

$(window).on("load", function () {
  $("#preloaderCont").fadeOut();
  $(
    "form .form-field #full_name, form .form-field #npersons, .form-field select#guests"
  ).val("");
  //$('form .form-field #npersons, .form-field select#guests').val('');
  $("form .form-field.fld-npersons").hide();

  $(".HeroSlider_wrapper").slick("slickPause");

  evtCount = 0;
  evTimer = setInterval(function () {
    evtCount++;
  }, 1000);

  if (!$("#ios_audio_lockscreen").length) {
    RunPrompter();
  }

  if (!IsMobile()) {
    $(".HeroSlider_wrapper").slick("slickPlay");
    PlayBgVido();
    BufferVideo(VideoBufferPercent);
  }
}); //end window load

$(window).on("resize", function () {
  appHeight(tplID);
});

function PlayBgVido() {
  if ($("video#vidbgcomp").length > 0) {
    $("#vidbgcomp")[0].play();
  }
}

function restartVideo() {
  var BGvideo = document.getElementById("vidbgcomp");
  BGvideo.currentTime = 0; // Seek to the beginning
  BGvideo.play(); // Start playback
}

function isBuffered(BufferLimit) {
  var vbLimit = BufferLimit;
  if (BufferLimit < 1) {
    vbLimit = 1;
  }
  if (BufferLimit > 99) {
    vbLimit = 99;
  }
  var BGvideo = document.getElementById("vidbgcomp");
  if (BGvideo.buffered.length > 0) {
    var buffered = BGvideo.buffered.end(0);
    var duration = BGvideo.duration;
    var bufferedPercentage = (buffered / duration) * 100;

    // Check if at least 50% of the video is buffered
    return bufferedPercentage >= BufferLimit;
  }
  return false; // Return false if no buffered data available
}

function BufferVideo(per_buffer_val) {
  if ($("video#vidbgcomp").length > 0) {
    var vidBufferTimer = setInterval(function () {
      if (isBuffered(per_buffer_val)) {
        console.log("buffer threshold reached.");
        clearInterval(vidBufferTimer);
        $("video#vidbgcomp").removeClass("hideVideo");
        restartVideo();
      } else {
        console.log("below buffer threshold");
      }
    }, 500);
  } //if video exist
}

function IsMobile() {
  var isMobile = false;
  if (
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent
    )
  ) {
    isMobile = true;
  }
  return isMobile;
}

function ShowPrompt() {
  $(".prompt").fadeIn();
  setTimeout(function () {
    $(".prompt").fadeOut();
  }, 5000);
}
function HideSwipeUp() {
  $(".prompt_swipeUp").fadeOut();
  $(".prompt_swipeUp").removeClass("isVisible");
}
function ShowSwipeUp() {
  $(".prompt_swipeUp").fadeIn();
  $(".prompt_swipeUp").addClass("isVisible");
  setTimeout(function () {
    HideSwipeUp();
  }, 6300);
}

function RunSwipeUpHelper() {
  HideSwipeUp();
  if ($("body").hasClass("ct_article")) {
    var _counter = 0;
    var scrollTimer = setInterval(function () {
      _counter++;
      if (_counter % 2 !== 0) {
        if ($("html, body").scrollTop() < 100) {
          ShowSwipeUp();
          //console.log('animation counter: '+_counter);
        } else {
          HideSwipeUp();
          clearInterval(scrollTimer);
        }
      }
    }, 6300); //repeat
  }

  $(window).on("scroll", function () {
    if ($("html, body").scrollTop() > 99) {
      HideSwipeUp();
      clearInterval(scrollTimer);
    }
  });
}

function RunPrompter() {
  intervalId_start = null;
  setTimeout(function () {
    ShowPrompt();
    intervalId_start = setInterval(function () {
      ShowPrompt();
    }, 12000); //repeat every 12 sec
  }, 2000);

  var storySlidesCount = $(".ct_slider #story_slider").slick(
    "getSlick"
  ).slideCount;
  var NeedPrompter = true;
  intervalId = null;

  $(".ct_slider #story_slider").on("afterChange", function () {
    $(".prompt").hide();
    clearInterval(intervalId_start);
    var userDidSwipe = false;

    var current_slide_classes = $(
      ".ct_slider #story_slider .slick-current"
    ).attr("class");
    var ExecludeSlides = ["rsvp", "endFrame"];

    var containsExcludedValue = ExecludeSlides.some((value) =>
      current_slide_classes.includes(value)
    );

    if (!containsExcludedValue) {
      console.log("The string does not contain any excluded value.");
      if (NeedPrompter) {
        var timeoutId = setTimeout(function () {
          if (!userDidSwipe) {
            console.log("User did not swipe in 5 seconds");
            // Your code to handle the case where the user didn't swipe
            ShowPrompt();
            intervalId = setInterval(function () {
              if (!userDidSwipe) {
                // Show the alert if the user hasn't swiped
                ShowPrompt();
              }
            }, 12000); //repeat every 12 sec
          }
        }, 5000);
      } //end if need prompter
    } else {
      console.log("The string contains an excluded value.");
      NeedPrompter = false;
    }
    // Set a timeout to check if the user swiped within 5 seconds

    $(".ct_slider #story_slider").on("afterChange", function () {
      // Clear the timeout and update the flag when the user swipes
      $(".prompt").hide();
      clearTimeout(timeoutId);
      clearInterval(intervalId);
      userDidSwipe = true;
      console.log("prompter afterChange");
    });

    $(".ct_slider #story_slider").on("beforeChange", function () {
      // Clear the timeout and update the flag when the user swipes
      $(".prompt").hide();
      console.log("prompter beforeChange");
    });
  });
} //end Runprompter

function togglePause() {
  var myAudio = document.getElementById("bgmusic");
  return myAudio.paused ? myAudio.play() : myAudio.pause();
}

function SlideBg() {
  var imgMob, imgDesk;
  $(".ct_slider .story_slide").each(function (index, element) {
    imgMob = $(this).data("bgmob");
    imgDesk = $(this).data("bgdesk");

    if ($(window).width() > 768) {
      $(this).css("background-image", "url(" + imgDesk + ")");
    } else {
      $(this).css("background-image", "url(" + imgMob + ")");
    }
  }); //end foreach
}

function appHeight(opt) {
  var idealHeight = 635;
  var windowHeight = $(window).height();
  var windowWidth = $(window).width();
  var appframe_height = windowHeight;
  var appframe_width = windowWidth;

  if (windowHeight < 490 && windowWidth > 700) {
    appframe_height = idealHeight;
  } else if (windowHeight > 636 && windowWidth > 769) {
    appframe_height = idealHeight;
  } else {
    appframe_height = windowHeight;
  }

  if (windowWidth > 680) {
    appframe_width = 375;
  } else {
    appframe_width = windowWidth;
  }

  $(".ct_slider #main .main_wrapper, .ct_slider .story_slide").css(
    "height",
    appframe_height + "px"
  );
  $("#main .main_wrapper, .ct_article #sec_intro").css(
    "width",
    appframe_width + "px"
  );
}

function RSVP_success_msg(msg) {
  $(".story_slide.rsvp .form-wrapper").addClass("onSuccess");
  $(".ajax_success_message_wrapper").html(msg);
}

function removeEmojis(inputString) {
  // Remove emojis using a regular expression
  return inputString.replace(/[\uD800-\uDFFF].|[\u200D\uFE0F]/g, "");
}

function customAutoPlay(PlaySpeed) {
  var storySlider = $(".ct_slider #story_slider");
  StorySlidesCount = $("#story_slider .story_slide").length - 1;

  var storySliderTimer = setInterval(function () {
    storySlider.slick("next");
  }, PlaySpeed);

  storySlider.on("afterChange", function () {
    CurrentSlideNum = storySlider.slick("slickCurrentSlide");
    if (CurrentSlideNum == StorySlidesCount - 1) {
      clearInterval(storySliderTimer);
    }
  });
}
