(function ($) {
    /**
     * DynamicSelect Logic.
     */
    function DynamicSelect(options)
    {
        this.options = options;

        this.init();
    }

    DynamicSelect.prototype = {

        init: function () {

        // loop through dynamic-select inputs
        $(this.options.id).each(function () {
            var structure = $(this).data('structure');
            var option_values = $(this).data('option-values');
            var selectors = $(this).data('selectors');
            var hide_next = $(this).data('hide-next');
            var previous_default_value = $(this).data('previous-default-value');
            console.log(structure);
            console.log(option_values);
            console.log(selectors);
            $(document).dynamicSelect({
                structure: structure,
                optionValues: option_values,
                selectors: selectors,
                hideNext: hide_next,
                previousDefaultValue: previous_default_value
            });

            // set values on edit mode
            selectors.forEach(function (el) {
                var value = $(el).data('value');
                if (!value) {
                    return;
                }

                $(el).val(value).change();
            });
        });
        }
    };

    new DynamicSelect({
        id: '[data-type="dynamic-select"]'
    });

})(jQuery);
