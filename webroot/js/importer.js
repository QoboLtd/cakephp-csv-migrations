;(function ($, document, window) {
    'use strict';

    /**
     * Importer plugin.
     */
    function Importer(element, options)
    {
        this.element = element;
        this.options = options;

        this.init();
    }

    Importer.prototype = {

        /**
         * Initialize method
         *
         * @return {undefined}
         */
        init: function () {
            var that = this;

            $(this.element).DataTable({
                stateSave: true,
                stateDuration: this.options.state_duration,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: this.options.url,
                    headers: {
                        'Authorization': 'Bearer ' + this.options.token
                    },
                    data: function (d) {
                        if (that.menus) {
                            d.menus = that.menus;
                        }
                        if (!jQuery.isEmptyObject(that.format)) {
                            d.format = that.format;
                        }
                        d.limit = d.length;
                        d.page = 1 + d.start / d.length;

                        return d;
                    },
                    dataFilter: function (d) {
                        d = jQuery.parseJSON(d);
                        d.recordsTotal = d.pagination.count;
                        d.recordsFiltered = d.pagination.count;

                        return JSON.stringify(d);
                    }
                }
            });
        }
    };

    $.fn.importer = function (options) {
        return this.each(function () {
            new Importer(this, options);
        });
    };

})(jQuery, document, window);
