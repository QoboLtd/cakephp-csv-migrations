(function ($) {
    /**
     * TinyMCE init
     */
    $(document).ready(function () {
        var config = {};
        if ('undefined' !== typeof tinymce_init_config) {
            config = tinymce_init_config;
        }

        tinyMCE.init(config);
    });

    // fix issue with link/image pop-up fields not working when tinymce is loaded within bootstrap modal
    // @link https://github.com/tinymce/tinymce/issues/782#issuecomment-151998981
    $(document).on('focusin', function (e) {
        if ($(e.target).closest('.mce-window').length) {
            e.stopImmediatePropagation();
        }
    });
})(jQuery);
