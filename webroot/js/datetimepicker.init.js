(function ($) {
    /**
     * DatetimePicker Logic.
     */
    function DatetimePicker()
    {
        var that = this;

        // initialize
        this.init();

        // Observe document for added datetime picker(s)
        dom_observer.added(document, function (nodes) {
            $(nodes).each(function () {
                $(this).find('[data-provide="datetimepicker"]').each(function () {
                    that.init();
                });
            });
        });
    }

    DatetimePicker.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            $('[data-provide="datetimepicker"]').each(function () {
                var startDate = this.value;
                if (!startDate) {
                    startDate = moment().format("YYYY-MM-DD 10:00");
                }

                // date range picker (used for datetime pickers)
                $(this).daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    timePicker: true,
                    drops: "down",
                    timePicker24Hour: true,
                    timePickerIncrement: 5,
                    locale: {
                        cancelLabel: "Clear",
                        format: "YYYY-MM-DD HH:mm",
                        firstDay: 1
                    },
                    startDate: startDate
                });

                $(this).on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                });
            });
        }
    };

    new DatetimePicker();

})(jQuery);
