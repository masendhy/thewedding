$(document).ready(function () {
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (scroll > 0) {
            $('.menu').addClass('nav--peach');
        } else {
            $('.menu').removeClass('nav--peach');
        }
    });
})

$('.picture').on('click', function() {
    var img = $(this).find('img').attr('src');
    $('#galery-preview').attr('src', img);
    $('#gallery-modal').modal('show');
});

$('.share').click(function(){
    $('.separator').toggleClass('toggled');
    $('.share-social').fadeToggle(500);
});