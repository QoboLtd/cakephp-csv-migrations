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
        this.fields = options.hasOwnProperty('fields') ? options.fields : {};
        this.menus = options.hasOwnProperty('menus') ? options.menus : {};
        this.format = options.hasOwnProperty('format') ? options.format : {};
        this.menu_property = options.hasOwnProperty('menu_property') ? options.menu_property : null;

        this.datatable();
    };

    ViewIndex.prototype.datatable = function() {
        var that = this;
        var columns = this._getTableColumns();

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
                    if (!jQuery.isEmptyObject(that.menus)) {
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
            },
            columns: columns
        });
    };

    /**
     * Method that organizes data fields in a columns array format
     * that is recognizable by DataTables, to be used as column data points.
     *
     * @link            https://www.datatables.net/manual/ajax
     * @return {object} Data columns
     */
    ViewIndex.prototype._getTableColumns = function() {
        var result = [];

        for (var k in this.fields) {
            result.push({'data': this.fields[k][0].name});
        }

        for (var j in this.menus) {
            var prefix = null;
            if (this.menu_property) {
                prefix = this.menu_property + '.';
            }
            result.push({'data': prefix + this.menus[j]});
        }

        return result;
    };

    view_index = new ViewIndex();

})(jQuery);
