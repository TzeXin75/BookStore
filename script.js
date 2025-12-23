

// Initialize Swiper
var swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    speed: 800,
});


// Hamburger toggle
$('#hamburger').click(function() {
    $('#navLinks').toggleClass('active');
});

// Submenu toggle for mobile
$('.main-category').click(function(e){
    if ($(window).width() <= 768) {
        e.preventDefault();
        $(this).siblings('.sub-menu').slideToggle(200);
    }
});

// Desktop hover for submenu
$('.nav-item').hover(
    function() {
        if ($(window).width() > 768) {
            $(this).children('.sub-menu').stop(true, true).slideDown(200);
        }
    },
    function() {
        if ($(window).width() > 768) {
            $(this).children('.sub-menu').stop(true, true).slideUp(200);
        }
    }
);




