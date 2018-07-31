(function ($) {
    /**
     * DatetimePicker Logic.
     */
    function DatetimePicker()
    {
        var that = this;

        this.format = {
            datetime: 'YYYY-MM-DD HH:mm',
            date: 'YYYY-MM-DD'
        };
        this.magicValues = [
            {id: '%%today%%', name: 'Today', value: [moment(), moment()]},
            {id: '%%yesterday%%', name: 'Yesterday', value: [moment().subtract(1, 'days'), moment().subtract(1, 'days')]},
            {id: '%%tomorrow%%', name: 'Tomorrow', value: [moment().add(1, 'days'), moment().add(1, 'days')]}
        ];

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
            var that = this;

            $('[data-provide="datetimepicker"]').each(function () {
                var input = this;
                // hidden input for magic value logic
                var hiddenInput = $('<input>').attr({name: $(this).attr('name'), type: 'hidden'});

                if ($(this).data('magic-value')) {
                    that.magicValues.forEach(function (item) {
                        if (input.value === item.id) {
                            hiddenInput.insertAfter(input);
                            $(hiddenInput).val(item.id);
                        }
                    });
                }

                var options = $(this).data();

                // date range picker (used for datetime fields)
                $(this).daterangepicker(that.getOptions(this, options), that.getCallback(this, hiddenInput));

                $(this).on('apply.daterangepicker', function (ev, picker) {
                    $(this).val(picker.startDate.format(picker.locale.format));
                });

                $(this).on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                });
            });
        },

        /**
         * DateTime picker options getter.
         *
         * @param {object} input Datetime picker input
         * @return {object}
         */
        getOptions: function (input, args) {
            var options = {
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                drops: 'down',
                autoUpdateInput: false,
                timePicker24Hour: true,
                timePickerIncrement: 5,
                locale: {
                    cancelLabel: 'Clear',
                    firstDay: 1
                }
            };

            if (args !== undefined) {
                options = Object.assign(options, args)
            }

            options.locale.format = options.timePicker ? this.format.datetime : this.format.date;

            if ($(input).data('magic-value')) {
                options.ranges = [];
                this.magicValues.forEach(function (item) {
                    // add custom ranges for magic value logic
                    options.ranges[item.name] = item.value;

                    // convert magic value to label, for example "%%today%%" becomes "Today"
                    if (input.value === item.id) {
                        $(input).val(item.name);
                    }
                });
            }

            if (! input.value && undefined !== $(input).data('default-value')) {
                options.startDate = moment().format($(input).data('default-value'));
                options.autoUpdateInput = true;
            }

            return options;
        },

        getCallback: function (input, hiddenInput) {
            var that = this;
            var result = function (start, end, label) {};

            if ($(input).data('magic-value')) {
                result = function (start, end, label) {
                    // always remove hidden input
                    hiddenInput.remove();

                    if ('Custom Range' === label) {
                        return;
                    }

                    $(input).val(label);

                    // attach hidden input on magic value selection
                    hiddenInput.insertAfter(input);
                    that.magicValues.forEach(function (item) {
                        if (label === item.name) {
                            $(hiddenInput).val(item.id);
                        }
                    });

                    $(input).val(label);
                };
            }

            return result;
        }
    };

    new DatetimePicker();

})(jQuery);
