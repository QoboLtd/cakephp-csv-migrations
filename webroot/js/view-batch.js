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

            $(that.options.target_id).each(function () {
                $(this).attr('disabled', true);
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.enable_id)
                    .replace('{{icon}}', 'check-circle')
                    .replace('{{action}}', 'Click to edit');
                $(this).parents(that.options.wrapper_id).append(msg);
            });

            $(document).on('click', that.options.disable_id, function () {
                var field = $(this).parent().find(that.options.target_id);
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.enable_id)
                    .replace('{{icon}}', 'check-circle')
                    .replace('{{action}}', 'Click to edit');
                $(field).parents(that.options.wrapper_id).append(msg);
                if (!$(field).attr('disabled')) {
                    $(field).attr('disabled', true);
                }
                $(this).remove();
            });

            $(document).on('click', that.options.enable_id, function () {
                var field = $(this).parent().find(that.options.target_id);
                var msg = that.helper.msg
                    .replace('{{id}}', that.helper.disable_id)
                    .replace('{{icon}}', 'times-circle')
                    .replace('{{action}}', 'Do not change');
                $(field).parents(that.options.wrapper_id).append(msg);
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
