$(document).ready(function() {
    var alertTimeout = window.setTimeout(function() {
        $(".alert.fade-out").fadeOut('slow');
    }, 10000);
});
$(document).ready(function() {
    $('.modal').modal({
        show: false
    });

    $("[data-confirmation]").click(function(e) {
        e.preventDefault();

        var dialog = $(".confirm#permanent");
        var form = $(e.currentTarget).parents('form');
        var button = $("[data-trigger]", dialog);

        dialog.modal('toggle');

        button.off();

        button.click(function() {
            return form.submit();
        });
    });
});
$(document).ready(function(e) {
    $('.collapse-header').click(function(e) {
        var header = $(e.currentTarget);

        var id = header.data('id');

        $('[data-id="'+ id +'"]:not(.collapse-header)').fadeToggle('slow');
    });
});
$(document).ready(function() {
    $('.radio-table input[type=radio]').change(function() {
        var parent = $(this).closest('.radio-table');

        var cssClass = parent.data('class');

        if (cssClass.length == 0) {
            cssClass = 'active';
        }

        $('tr', parent).removeClass(cssClass);
        $(this).closest('tr').addClass(cssClass);
    });
});
//# sourceMappingURL=account.js.map
