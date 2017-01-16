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
            // date range picker (used for datetime pickers)
            $('[data-provide="datetimepicker"]').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                drops: "up",
                timePicker12Hour: false,
                timePickerIncrement: 5,
                format: "YYYY-MM-DD HH:mm"
            });
        }
    };

    new DatetimePicker();

})(jQuery);
