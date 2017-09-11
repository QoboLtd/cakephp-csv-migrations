$(document).ready(function () {
    /** letting the cancel avoid html5 validation and redirect back */
    $('.remove-client-validation').click(function () {
        $(this).parent('form').attr('novalidate', 'novalidate');
    });

    /**
     * Trigger deletion of the record from the dynamic DataTables entries.
     */
    $('body').on('click','a[data-type="ajax-delete-record"]', function (e) {
        e.preventDefault();
        var hrefObj = this;

        if (confirm($(this).data('confirm-msg'))) {
            $.ajax({
                url: $(this).attr('href'),
                method: 'DELETE',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + api_options.token
                },
                success: function (data) {
                    //traverse upwards on the tree to find table instance and reload it
                    var table = $(hrefObj).closest('.table-datatable').DataTable();
                    table.ajax.reload();

                }
            });
        }
    });

});
