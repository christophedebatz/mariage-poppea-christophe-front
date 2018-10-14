/*============================
   js index
==============================

==========================================*/

$(document).ready(function () {
  var swiper = new Swiper('.swiper-container', {
    loop: true,
    speed: 2000,

    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    }
  });

  /*================================
  Window Load
  ==================================*/
  $(window).on('load', function () {
    smoothScrolling($(".main-menu nav ul li a[href^='#']"), headerHeight);
    smoothScrolling($(".scrollup a[href^='#']"), 0);
    $('.slider-two').addClass('scontent_loaded');
    $('.slider-parallax').addClass('scontent_loaded');
    sliderLoadedAddClass();
    preloader()
  });


  /*================================
  Preloader
  ==================================*/

  /*------------------------------------------
    = HIDE PRELOADER
-------------------------------------------*/
  function preloader() {
    if ($('.preloader').length) {
      $('.preloader').delay(100).fadeOut(500, function () {
      });
    }
  }

  /*================================
   sticky-header
   ==================================*/
  $(window).scroll(function () {

    if ($(window).scrollTop() > 10) {
      $('.sticky-header').addClass('sticky'),
        $('.scrollup').addClass('show_hide');
    } else {
      $('.sticky-header').removeClass('sticky'),
        $('.scrollup').removeClass('show_hide');
    }

  });

  /*================================
   Gift-carousel
   ==================================*/
  function gift_carousel() {
    var owl = $(".Gift-carousel");
    owl.owlCarousel({
      loop: true,
      margin: 0,
      navText: false,
      nav: false,
      items: 5,
      smartSpeed: 1000,
      dots: false,
      autoplay: true,
      autoplayTimeout: 3000,
      responsive: {
        0: {
          items: 3
        },
        480: {
          items: 2
        },
        760: {
          items: 4
        },
        1080: {
          items: 4
        }
      }
    });
  }

  gift_carousel();

  /*================================
  slicknav
  ==================================*/
  $('#nav_mobile_menu').slicknav({
    prependTo: "#mobile_menu"
  });

  /*------------------------------------------
      = RSVP FORM SUBMISSION
  -------------------------------------------*/
  if ($("#rsvp-form").length) {
    $("#rsvp-form").validate({
      rules: {
        name: {
          required: true,
          minlength: 2
        },
        email: "required",

        guest: {
          required: true
        },

        events: {
          required: true
        }

      },

      messages: {
        name: "Please enter your name",
        email: "Please enter your email",
        guest: "Select your number of guest",
        events: "Select your event list"
      },

      submitHandler: function (form) {
        $("#loader").css("display", "inline-block");
        $.ajax({
          type: "POST",
          url: "mail.php",
          data: $(form).serialize(),
          success: function () {
            $("#loader").hide();
            $("#success").slideDown("slow");
            setTimeout(function () {
              $("#success").slideUp("slow");
            }, 3000);
            form.reset();
          },
          error: function () {
            $("#loader").hide();
            $("#error").slideDown("slow");
            setTimeout(function () {
              $("#error").slideUp("slow");
            }, 3000);
          }
        });
        return false; // required to block normal submit since you used ajax
      }

    });
  }

  /*================================
  slider-area content effect
  ==================================*/
  function sliderLoadedAddClass() {
    $('.slider-two').addClass('scontent_loaded');
    $('.slider-parallax').addClass('scontent_loaded');
  }


  /*================================
    Isotope Portfolio
   ==================================*/
  $('.grid').imagesLoaded(function () {

    // filter items on button click
    $('.gallery-menu').on('click', 'button', function () {
      var filterValue = $(this).attr('data-filter');
      $grid.isotope({
        filter: filterValue
      });
    });

    // init Isotope
    var $grid = $('.grid').isotope({
      itemSelector: '.grid-item',
      percentPosition: true,
      masonry: {
        // use outer width of grid-sizer for columnWidth
        columnWidth: '.grid-item',
      }
    });


  });

  $('.gallery-menu button').on('click', function () {
    $('.gallery-menu button').removeClass('active');
    $(this).addClass('active');
  });


  /*------------------------------------------
      = COUNTDOWN CLOCK
  -------------------------------------------*/
  function countdown(endDate, callback) {
    let id;

    id = setInterval(() => {
      let {days, hours, minutes, seconds} = callback(endDate, id);
      $('#clock').html(
        '<div class="box"><div class="date">' + parseInt(days, 10) + '</div> <span>JOURS</span> </div>' +
        '<div class="box"><div class="date">' + ("0" + hours).slice(-2) + '</div> <span>HEURES</span> </div>' +
        '<div class="box"><div class="date">' + ("0" + minutes).slice(-2) + '</div> <span>MINUTES</span> </div>' +
        '<div class="box"><div class="date">' + ("0" + seconds).slice(-2) + '</div> <span>SECONDES</span> </div>');
    }, 1000);
  }

  countdown('24/08/2019', (endtime, id) => {
    let t = new Date("Aug 24, 2019 17:00:00").getTime() - new Date().getTime();
    if (t <= 0) {
      clearInterval(id);
    }
    let seconds = Math.floor((t / 1000) % 60);
    let minutes = Math.floor((t / 1000 / 60) % 60);
    let hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    let days = Math.floor(t / (1000 * 60 * 60 * 24));
    return {
      days,
      hours,
      minutes,
      seconds
    };
  });


  /*================================
   Variable Initialize
  ==================================*/
  var headerHeight = $('.header-area').innerHeight();


//. smooth scrolling
  function smoothScrolling($links, $topGap) {
    var links = $links;
    var topGap = $topGap;

    links.on("click", function () {
      if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
        var target = $(this.hash);
        target = target.length ? target : $("[name=" + this.hash.slice(1) + "]");
        if (target.length) {
          $("html, body").animate({
            scrollTop: target.offset().top - topGap
          }, 1000, "easeInOutExpo");
          return false;
        }
      }
      return false;
    });
  }

//.scrolltop
  $(function () {
    $('.scrollup').on('click', function (event) {
      var $anchor = $(this);
      $('html, body').stop().animate({
        scrollTop: $($anchor.attr('href')).offset().top - 1
      }, 1000, 'easeInOutExpo');
      event.preventDefault();
    });
    $('body').attr('id', 'scrolltop');
  });


  /*================================
  /*================================
  Magnific Popup
  ==================================*/
  if ($(".expand-img").length) {
    $('.expand-img').magnificPopup({
      type: 'image',
      gallery: {
        enabled: true
      }
    });

    $('.expand-video').magnificPopup({
      type: 'iframe',
      gallery: {
        enabled: true
      }
    });
  }
});