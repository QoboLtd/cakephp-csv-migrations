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
        this.options = options;
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
        this._batchActions();
    };

    ViewIndex.prototype._batchActions = function () {
        var that = this;
        $(this.options.batch.delete_id).click(function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete the selected records?')) {
                return;
            }

            that._createAndSubmitBatchForm('delete');
        });
        $(this.options.batch.edit_id).click(function (e) {
            e.preventDefault();

            that._createAndSubmitBatchForm('post');
        });
    };

    ViewIndex.prototype._createAndSubmitBatchForm = function (type) {
        var $form = $('<form method="post" action="' + this.options.batch.url + '"></form>');

        $form.append('<input type="hidden" name="_method" value="' + type.toUpperCase() + '" />');
        $(this.options.table_id + ' tr.selected').each(function () {
            $form.append('<input type="text" name="batch[ids][]" value="' + $(this).attr('data-id') + '">');
        });

        $form.submit();
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
            order: [
                [1, "asc"]
            ],
            columnDefs: [
                {targets: [-1, 0], orderable: false},
                {targets: [0], className: 'select-checkbox'}
            ],
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            createdRow: function ( row, data, index ) {
                $(row).attr('data-id', data[0]);
                $('td', row).eq(0).text('');
            },
            deferRender: true,
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

                    d.order[0].column -= 1;

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

        table.on('select', function () {
            $('#batch-button').attr('disabled', false);
        });

        table.on('deselect', function (e, dt, type, indexes) {
            if (0 === table.rows('.selected').count()) {
                $('#batch-button').attr('disabled', true);
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
