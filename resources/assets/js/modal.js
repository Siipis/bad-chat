$(document).ready(function() {
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