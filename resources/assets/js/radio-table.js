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