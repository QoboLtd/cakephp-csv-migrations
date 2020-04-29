$(document).ready(function() {

    $("input[type=file]").each(function () {
        var files = $(this).data('files');
        var name = $(this).attr('name');
        var that = this;

        var ids = [];
        var initialPreview = [];
        var initialPreviewConfig = [];
        if(files) {

            //initialPreview
            for (const file of files) {
              initialPreview.push(file.path);
            }

            //initialPreviewConfig
            for (const file of files) {
                initialPreviewConfig.push({
                    downloadUrl:file.path,
                    size: file.size,
                    key:file.id,
                    url: '/api/file-storage/delete/' + file.id
                });
            }
        }


        $(this).fileinput({
        initialPreview: initialPreview,
            initialPreviewAsData: true,
            initialPreviewConfig: initialPreviewConfig,
            overwriteInitial: false,
            maxFileSize: 100,
            initialCaption: name,
            ajaxDeleteSettings: {
                type: 'delete',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + api_options.token
                },
            },
            ajaxSettings: {
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + api_options.token
                },
            }
        }).on('fileuploaded', function(event, previewId, index, fileId) {
            console.log('File uploaded', previewId, index, fileId);
        });

    });
});
