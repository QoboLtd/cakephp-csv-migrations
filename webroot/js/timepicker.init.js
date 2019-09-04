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

        let items = $("body").find('[data-provide="timepicker"]')
        if (items.length) {
            items.each(function () {
                $(this).data('timepicker').update()
            })
        }
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
            $('[data-provide="timepicker"]').timepicker(defaults).on('changeTime.timepicker show.timepicker', function (e) {
                // bugfix to prevent one digit hour
                let hours = e.time.hours
                let minutes = e.time.minutes

                if (hours < 10) {
                    hours = '0' + hours
                }
                if (minutes < 10) {
                    minutes = '0' + minutes
                }
                $(e.currentTarget).val(hours + ':' + minutes);
            });

        }
    };

    new TimePicker();

})(jQuery);
