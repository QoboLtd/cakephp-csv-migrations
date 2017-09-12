var view_index = view_index || {};

(function ($) {
    /**
     * View Index Logic.
     *
     * @param {object} options
     */
    function ViewIndex()
    {}

    /**
     * Initialize method.
     *
     * @return {undefined}
     */
    ViewIndex.prototype.init = function (options) {
        this.api_url = options.hasOwnProperty('api_url') ? options.api_url : null;
        this.api_ext = options.hasOwnProperty('api_ext') ? options.api_ext : null;
        this.api_token = options.hasOwnProperty('api_token') ? options.api_token : null;
        this.table_id = options.hasOwnProperty('table_id') ? options.table_id : null;
        this.menus = options.hasOwnProperty('menus') ? options.menus : false;
        this.format = options.hasOwnProperty('format') ? options.format : {};
        this.extras = options.hasOwnProperty('extras') ? options.extras : {};
        this.state_duration = options.hasOwnProperty('state_duration') ? options.state_duration : 0;

        var table = this.datatable();
        this._handleDeleteLinks(table);
    };

    /**
     * Initialize table using datatables.
     *
     * @return {object} Datatables Table object
     */
    ViewIndex.prototype.datatable = function () {
        var that = this;

        var table = $(this.table_id).DataTable({
            stateSave: true,
            stateDuration: that.state_duration,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: that.api_url + '.' + that.api_ext,
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
                data: function (d) {
                    if (that.menus) {
                        d.menus = that.menus;
                    }
                    if (!jQuery.isEmptyObject(that.format)) {
                        d.format = that.format;
                    }

                    if (that.extras) {
                        d = $.extend({}, d, that.extras);
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

        return table;
    };

    /**
     * Method that handles delete links.
     *
     * @param  {type} table Datatables Table object
     * @return {undefined}
     */
    ViewIndex.prototype._handleDeleteLinks = function (table) {
        var that = this;

        $(this.table_id + ' tbody').on('click', '[data-type="ajax-delete-record"]', function (e) {
            e.preventDefault();

            if (confirm($(this).data('confirm-msg'))) {
                $.ajax({
                    url: $(this).attr('href'),
                    method: 'DELETE',
                    dataType: 'json',
                    contentType: 'application/json',
                    headers: {
                        'Authorization': 'Bearer ' + that.api_token
                    },
                    success: function (data) {
                        // refresh datatable on successful deletion
                        table.ajax.reload();
                    }
                });
            }
        });
    };

    view_index = new ViewIndex();

})(jQuery);
