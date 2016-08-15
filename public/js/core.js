$(document).ready(function() {
    console.log('Alert automation loaded');

    var alertTimeout = window.setTimeout(function() {
        $(".alert.fade-out").fadeOut('slow');
    }, 10000);
});
$(document).ready(function() {
    console.log('Confirmation dialogs loaded');

    $("[data-confirmation]").click(function(e) {
        e.preventDefault();

        var dialog = $(".confirm#permanent");
        var form = $(e.currentTarget).parents('form');
        var button = $("[data-trigger]", dialog);

        dialog.modal();

        button.off();

        button.click(function() {
            return form.submit();
        });
    });
});
$(document).ready(function(e) {
    console.log('Collapse lists loaded');

    $('.collapse-header').click(function(e) {
        var header = $(e.currentTarget);

        var id = header.data('id');

        $('[data-id="'+ id +'"]:not(.collapse-header)').fadeToggle('slow');
    });
});
//# sourceMappingURL=core.js.map
