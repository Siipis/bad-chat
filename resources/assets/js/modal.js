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