$(document).ready(function(e) {
    $('.collapse-header').click(function(e) {
        var header = $(e.currentTarget);

        var id = header.data('id');

        $('[data-id="'+ id +'"]:not(.collapse-header)').fadeToggle('slow');
    });
});