// Sticky Header
$(window).scroll(function() {

    if ($(window).scrollTop() > 150) {
        $('.main_h').addClass('sticky');
        document.getElementById('logo').style.display = 'none'
    } else {
        $('.main_h').removeClass('sticky');
        document.getElementById('logo').style.display = 'block'
    }
});

// if you want to add stick menu on custom div scroll

$(window).scroll(function() {

    if ($("your_div").scrollTop() > 100) {
        $('.main_h').addClass('sticky');
    } else {
        $('.main_h').removeClass('sticky');
    }
});
