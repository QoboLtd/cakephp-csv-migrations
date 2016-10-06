var view_index = view_index || {};

(function($) {
    /**
     * View Index Logic.
     *
     * @param {object} options
     */
    function ViewIndex() {}

    /**
     * Initialize method.
     *
     * @return {void}
     */
    ViewIndex.prototype.init = function(options) {
        this.api_url = options.hasOwnProperty('api_url') ? options.api_url : null;
        this.api_ext = options.hasOwnProperty('api_ext') ? options.api_ext : null;
        this.api_token = options.hasOwnProperty('api_token') ? options.api_token : null;
        this.table_id = options.hasOwnProperty('table_id') ? options.table_id : null;
        this.menus = options.hasOwnProperty('menus') ? options.menus : false;
        this.format = options.hasOwnProperty('format') ? options.format : {};

        this.datatable();
    };

    ViewIndex.prototype.datatable = function() {
        var that = this;

        $(this.table_id).DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: that.api_url + '.' + that.api_ext,
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
                data: function(d) {
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
                dataFilter: function(d) {
                    d = jQuery.parseJSON(d);
                    d.recordsTotal = d.pagination.count;
                    d.recordsFiltered = d.pagination.count;

                    return JSON.stringify(d);
                }
            }
        });
    };

    view_index = new ViewIndex();

})(jQuery);
