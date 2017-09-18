;(function ($, document, window) {
    'use strict';

    /**
     * Batch View plugin.
     */
    function ViewBatch(element, options)
    {
        this.element = element;
        this.options = options;
        this.helper = {
            'msg': '<span class="help-block" {{id}} style="cursor:pointer;"><i class="fa fa-{{icon}}"></i> {{action}}</span>',
            'enable_id': 'data-batch="enable"',
            'disable_id': 'data-batch="disable"'
        };

        this.init();
    }

    ViewBatch.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            var that = this;

            $('*[data-batch="field"]').each(function () {
                $(this).attr('disabled', true);
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.enable_id)
                    .replace('{{icon}}', 'check-circle')
                    .replace('{{action}}', 'Click to edit');
                $(this).after(msg);
            });

            $(document).on('click', '*[data-batch="disable"]', function () {
                var field = $(this).parent().find('*[data-batch="field"]');
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.enable_id)
                    .replace('{{icon}}', 'check-circle')
                    .replace('{{action}}', 'Click to edit');
                $(field).after(msg);
                if (!$(field).attr('disabled')) {
                    $(field).attr('disabled', true);
                }
                $(this).remove();
            });

            $(document).on('click', '*[data-batch="enable"]', function () {
                var field = $(this).parent().find('*[data-batch="field"]');
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.disable_id)
                    .replace('{{icon}}', 'times-circle')
                    .replace('{{action}}', 'Do not change');
                $(field).after(msg);
                if ($(field).attr('disabled')) {
                    $(field).attr('disabled', false);
                    $(field).focus();
                }
                $(this).remove();
            });
        }
    };

    $.fn.viewBatch = function (options) {
        return this.each(function () {
            new ViewBatch(this, options);
        });
    };

})(jQuery, document, window);
