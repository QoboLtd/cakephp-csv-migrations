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
            var format = 'YYYY-MM-DD HH:mm';
            $('[data-provide="datetimepicker"]').each(function () {
                var options = {
                    singleDatePicker: true,
                    showDropdowns: true,
                    timePicker: true,
                    drops: 'down',
                    timePicker24Hour: true,
                    timePickerIncrement: 5,
                    locale: {
                        cancelLabel: 'Clear',
                        format: format,
                        firstDay: 1
                    }
                };

                var defaultValue = $(this).data('default-value');
                if (!this.value && undefined !== defaultValue) {
                    options.startDate = moment().format(defaultValue);
                } else {
                    options.autoUpdateInput = false;
                }

                // date range picker (used for datetime fields)
                $(this).daterangepicker(options);

                $(this).on('apply.daterangepicker', function (ev, picker) {
                    $(this).val(picker.startDate.format(format));
                });

                $(this).on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                });
            });
        }
    };

    new DatetimePicker();

})(jQuery);
