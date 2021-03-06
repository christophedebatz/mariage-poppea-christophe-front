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
  

  function debounce (func, wait, immediate) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    }
  }

  $(function () {

    let self = this;
    let _guests = [];
    self.currentUser = null;
    
    $('#loader').show();
    $.ajaxSetup({ cache: false });
    $.getJSON('booking.php?userslist', function (guests) {
      let options = $("#guest");
      _guests = guests.sort(function (g1, g2) {
        return g1.fullName.localeCompare(g2.fullName);
      });
      $('#loader').hide();
      _guests.forEach(function (guest) {
        let fullName = guest.fullName
        if (guest.vip) {
          fullName += ' - témoin';
        }
        options.append($("<option />").val(guest.userId).text(fullName));
      });

      document.getElementById('guest').addEventListener('change', function () {
        $('#loader').show();
        let userId = parseInt(document.getElementById('guest').value);
        let userName = $('#guest option:selected').text().replace(' - témoin', '');
        $('#container-brunch').hide();
        $('#container-eglise').hide();
        $('#container-diner').hide();

        $.getJSON(`booking.php?userId=${userId}`, function (book) {
          if (book.reservation && book.user) {
            self.currentUser = book.user;
            let reservation = book.reservation;
            $('#response-eglise').prop('checked', reservation.eglise);
            $('#response-diner').prop('checked', reservation.diner);
            $('#response-brunch').prop('checked', reservation.brunch);
            $('#address').prop('value', reservation.address);
            writeText(self.currentUser, reservation);
          }
        })
          .error(function (e) {
            let selectedGuest = null;
            for (let i = 0; i < _guests.length; i++) {
              let guest = _guests[i];
              if (guest.userId === userId) {
                selectedGuest = guest;
                break;
              }
            }
            writeText(selectedGuest, null);
            $('#response-eglise').prop('checked', false);
            $('#response-diner').prop('checked', false);
            $('#response-brunch').prop('checked', false);
            $('#address').prop('value', '');
            if (selectedGuest.eglise) $('#container-eglise').show();
            if (selectedGuest.diner) $('#container-diner').show();
            if (selectedGuest.brunch) $('#container-brunch').show();
            self.currentUser = selectedGuest;
          })
          .complete(function () {
            $('#loader').hide();
            $('#reservations').show();
            document.getElementById('address').addEventListener('blur', function (e) {
              e.preventDefault();
              changeListener(self.currentUser);
            });
            var saveTypedAddress = debounce(function() {
                changeListener(self.currentUser, false);
              }, 500)
            document.getElementById('address').addEventListener('keyup', saveTypedAddress);
            document.getElementById('response-eglise').addEventListener('change', function (e) {
              e.stopImmediatePropagation();
              changeListener(self.currentUser);
            });
            document.getElementById('response-diner').addEventListener('change', function (e) {
              e.stopImmediatePropagation();
              changeListener(self.currentUser);
            });
            document.getElementById('response-brunch').addEventListener('change', function (e) {
              e.stopImmediatePropagation();
              changeListener(self.currentUser);
            });
          });
      });

    function changeListener(user, displayToast = true) {
      let eglise = $('#response-eglise').prop('checked');
      let diner = $('#response-diner').prop('checked');
      let brunch = $('#response-brunch').prop('checked');
      let address = $('#address').val();
      postResponse(user.userId, brunch, eglise, diner, address, function (err, data) {
        if (!err) {
          let book = {
            address,
            user,
            reservation: {
              brunch,
              eglise,
              diner
            }
          };
          writeText(book.user, book.reservation);
          if (displayToast) {
            $.toast({
                heading: 'Bien joué !',
                text: 'Réponse enregistrée avec succès !',
                showHideTransition: 'slide',
                icon: 'success',
                position: 'top-right',
                loaderBg: '#b6d65a'
            });
          }
        } else {
          $.toast({
            heading: 'Error',
            text: 'Erreur dans lors de l\'enregistrement de votre réponse. Veuillez réessayer et/ou nous contacter...',
            showHideTransition: 'slide',
            icon: 'error',
            position: 'top-right',
        });
        }
      });
    }

    function postResponse(userId, brunch, eglise, diner, address, callback) {
      $.ajax({
        type: 'POST',
        url: `booking.php?bookUserId=${userId}`,
        data: JSON.stringify({'address': address, 'brunch': brunch ? 1 : 0, 'eglise': eglise ? 1 : 0, 'diner': diner ? 1 : 0}),
        success: function (data) {
          callback(false, data);
        },
        error: callback,
        contentType: "application/json; charset=utf-8",
        dataType: 'json'
      });
    }
  });
});


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
    let t = new Date("Aug 24, 2019 16:00:00").getTime() - new Date().getTime();
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
