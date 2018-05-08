(function ($) {
    /**
     * DateePicker Logic.
     */
    function DatePicker()
    {
        var that = this;

        this.magicValueClass = 'datepicker-magic-value';
        this.magicValues = [
            {id: '%%today%%', name: 'Today'},
            {id: '%%yesterday%%', name: 'Yesterday'},
            {id: '%%tomorrow%%', name: 'Tomorrow'}
        ];

        // initialize
        this.init();

        // Observe document for added time picker(s)
        dom_observer.added(document, function (nodes) {
            $(nodes).each(function () {
                $(this).find('[data-provide="datepicker"]').each(function () {
                    that.init();
                });
            });
        });
    }

    DatePicker.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            var that = this;

            $('[data-provide="datepicker"]').each(function () {
                var input = this;
                // hidden input for magic value logic
                var hiddenInput = $('<input>').attr({name: $(this).attr('name'), type: 'hidden'});

                if ($(this).data('magic-value')) {
                    // remove hidden input
                    hiddenInput.remove();

                    that.magicValues.forEach(function (item) {
                        if (input.value !== item.id) {
                            return;
                        }

                        hiddenInput.insertAfter(input);
                        $(hiddenInput).val(item.id);
                        $(input).val(item.name);
                    });
                }

                // datepicker (used for date fields)
                var datepicker = $(this).datepicker(that.getOptions(this));

                if ($(this).data('magic-value')) {
                    that.magicValueEvents(datepicker, hiddenInput);
                }

            });
        },

        /**
         * Date picker options getter.
         *
         * @param {object} input Date picker input
         * @return {object}
         */
        getOptions: function (input) {
            var options = { format: 'yyyy-mm-dd', autoclose: true, weekStart: 1 };

            if ($(input).data('magic-value')) {
                options.forceParse = false;
            }

            return options;
        },

        magicValueEvents: function (datepicker, hiddenInput) {
            var that = this;

            datepicker.on('changeDate', function (e) {
                hiddenInput.remove();
            });

            datepicker.on('show', function (e) {
                var input = this;

                $('.datepicker .' + that.magicValueClass).remove();

                that.magicValues.forEach(function (item) {
                    $('.datepicker tfoot').append(
                        '<tr class="' + that.magicValueClass + '">' +
                            '<th colspan="7" data-id="' + item.id + '">' + item.name + '</th>' +
                        '</tr>'
                    );
                });

                $('.datepicker tfoot .' + that.magicValueClass + ' th').on('click', function () {
                    var th = this;

                    that.magicValues.forEach(function (item) {
                        if (item.id !== $(th).data('id')) {
                            return;
                        }

                        hiddenInput.insertAfter(input);
                        $(hiddenInput).val(item.id);
                        $(input).val(item.name);
                    });

                    datepicker.datepicker('hide');
                });
            });
        }
    };

    $(document).off('.datepicker.data-api');

    new DatePicker();

})(jQuery);
