$(document).ready(function () {
    /** letting the cancel avoid html5 validation and redirect back */
    $('.remove-client-validation').click(function () {
        $(this).parent('form').attr('novalidate', 'novalidate');
    });
});
