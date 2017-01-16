(function ($) {
    /**
     * iCheck Logic.
     */
    function iCheck()
    {
        var that = this;

        // initialize
        this.init();

        // Observe document for added checkbox(es) / radio(s)
        dom_observer.added(document, function (nodes) {
            $(nodes).each(function () {
                $(this).find('input[type="checkbox"], input[type="radio"]').each(function () {
                    that.init();
                });
            });
        });
    }

    iCheck.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            // iCheck for checkbox and radio inputs
            $('input[type="checkbox"].flat, input[type="radio"].flat').iCheck({
                checkboxClass: 'icheckbox_flat',
                radioClass: 'iradio_flat'
            });
            $('input[type="checkbox"].futurico, input[type="radio"].futurico').iCheck({
                checkboxClass: 'icheckbox_futurico',
                radioClass: 'iradio_futurico'
            });
            $('input[type="checkbox"].line, input[type="radio"].line').iCheck({
                checkboxClass: 'icheckbox_line',
                radioClass: 'iradio_line'
            });
            $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
            $('input[type="checkbox"].polaris, input[type="radio"].polaris').iCheck({
                checkboxClass: 'icheckbox_polaris',
                radioClass: 'iradio_polaris'
            });
            $('input[type="checkbox"].square, input[type="radio"].square').iCheck({
                checkboxClass: 'icheckbox_square',
                radioClass: 'iradio_square'
            });
        }
    };

    new iCheck();

})(jQuery);
