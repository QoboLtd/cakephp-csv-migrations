(function ($) {
    /**
     * TimePicker Logic.
     */
    function TimePicker()
    {
        var that = this;

        // initialize
        this.init();

        // Observe document for added time picker(s)
        dom_observer.added(document, function (nodes) {
            $(nodes).each(function () {
                $(this).find('[data-provide="timepicker"]').each(function () {
                    that.init();
                });
            });
        });
    }

    TimePicker.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            // time picker
            $('[data-provide="timepicker"]').timepicker({
                showMeridian: false,
                minuteStep: 5,
                defaultTime: false
            });
        }
    };

    new TimePicker();

})(jQuery);
