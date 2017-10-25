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

        if (options.datatable) {
            this._handleDeleteLinks();
        }
    };

    /**
     * Method that handles delete links.
     *
     * @param  {type} table Datatable instance
     * @return {undefined}
     */
    ViewIndex.prototype._handleDeleteLinks = function () {
        var that = this;

        var table = this.options.datatable;

        $('#' + table.table().node().id + ' tbody').on('click', '[data-type="ajax-delete-record"]', function (e) {
            e.preventDefault();

            if (confirm($(this).data('confirm-msg'))) {
                $.ajax({
                    url: $(this).attr('href'),
                    method: 'DELETE',
                    dataType: 'json',
                    contentType: 'application/json',
                    headers: {
                        'Authorization': 'Bearer ' + that.options.token
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
