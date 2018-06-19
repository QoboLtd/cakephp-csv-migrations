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
            var defaults = {
                showMeridian: false,
                minuteStep: 5,
                defaultTime: false
            };

            var opts = $('[data-provide="timepicker"]').data();

            if (opts !== undefined) {
                defaults = Object.assign(defaults, opts);
            }

            // time picker
            $('[data-provide="timepicker"]').timepicker(defaults);
        }
    };

    new TimePicker();

})(jQuery);
