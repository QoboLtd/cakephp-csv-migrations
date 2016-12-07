$(document).ready(function () {
    'use strict';

    var FileInput = function (files, name, field) {
        this.html = this.staticHtml();
        this.api_token = api_options.hasOwnProperty('token') ? api_options.token : null;
        this.options = {};
        if (typeof files === 'object') {
            this.initialPreview(files);
            this.initialPreviewConfig(files);
            this.createFromExisting(files, field);
        } else {
            this.createNew(field);
        }
        field.on('change', function (e) {
            //Trigger the updateFiles Event and pass all the collected uploads
            $(document).trigger('updateFiles', [e.target.files, $(this).attr('name')]);
            console.log('change files');
        });
    };

    /**
     * Static HTML
     *
     * @return object
     */
    FileInput.prototype.staticHtml = function () {
        return {
            previewOtherFile: "<div class='file-preview-text'><h2>" +
                "<i class='glyphicon glyphicon-file'></i></h2>" +
                "<a href='%%url%%' target='_blank'>View file</a></div>",
            img: "<img class='img-responsive' src='%%url%%' alt='img-preview' />",
            trash: "<i class=\"glyphicon glyphicon-trash\"></i>",
            icons: {
                docx: '<i class="fa fa-file-word-o text-primary"></i>',
                xlsx: '<i class="fa fa-file-excel-o text-success"></i>',
                pptx: '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                jpg: '<i class="fa fa-file-photo-o text-warning"></i>',
                pdf: '<i class="fa fa-file-pdf-o text-danger"></i>',
                zip: '<i class="fa fa-file-archive-o text-muted"></i>',
            }
        };
    };

    /**
     * Preview initial preview of the upload field.
     *
     * @param string url
     */
    FileInput.prototype.setInitialPreview = function (url) {
        var initialPreview = '';
        if (this.isImg(url)) {
            initialPreview = this.html.img;
        } else {
            initialPreview = this.html.previewOtherFile;
        }
        this.preview = initialPreview.replace('%%url%%', url);
    };

    /**
     * Check for image on the provided URL.
     *
     * @param string
     * @return bool
     */
    FileInput.prototype.isImg = function (url) {
        return (url.match(/\.(jpeg|jpg|gif|png)$/) != null);
    };

    /**
     * Builds the delete URL
     * @param string name of the field.
     */
    FileInput.prototype.setDeleteUrl = function (name) {
        var matches = name.match(/\[(\w+)\]\[(\w+)\]/);
        var fieldName = matches[2];
        this.deleteUrl = window.location.href.replace('edit', 'unlinkUpload') + '/' + fieldName;
    };

    /**
     * Plugin's default options.
     * @NOTE: fileInputOptions is defined globally on the page
     *
     * @return object Plugin's default options
     */
    FileInput.prototype.defaults = function () {
        if (fileInputOptions.defaults !== undefined) {
            return fileInputOptions.defaults;
        } else {
            console.log('undefined');
            return {
                showUpload: true,
                showRemove: false,
                showUploadedThumbs: false,
                uploadAsync: true,
                dropZoneEnabled: false,
                showUploadedThumbs: false,
                fileActionSettings: {
                    showUpload: false,
                    showZoom: false,
                },
                maxFileCount: 30,
                fileSizeGetter: true,
                maxFileSize: 2000,
                uploadUrl: '/api/file-storages/upload'
            };
        }
    };

    // @NOTE: initialPreviewConfig - is a global varible
    FileInput.prototype.initialPreviewConfig = function (files) {
        var config = {};
        this.options.initialPreviewConfig = new Array;
        console.log('foo - initialPreviewConfig');
        for (var i in files) {
            var file = files[i];
            var ipcOptions = $.extend({}, config, {key: i, url: fileInputOptions.initialPreviewConfig.url + file.id});
            this.options.initialPreviewConfig.push(ipcOptions);
        }
    };

    FileInput.prototype.initialPreview = function (files) {
        this.options.initialPreview = new Array;
        console.log('initial Preview');
        for (var i in files) {
            var file = files[i];
            this.options.initialPreview.push(file.path);
        }
    };

    /**
     * Creates new instance of fileinput.
     *
     * @param  jQueryObject inputField to build the library on
     */
    FileInput.prototype.createNew = function (inputField) {
        var createNew = {};
        var options = $.extend({}, this.defaults(), createNew);
        /*
        inputField.fileinput(options).on("filebatchselected", function(event, files){
            //$(document).trigger('updateFiles', [e.target.files, $(this).attr('name')]);
            console.log(event);
            console.log(files);
        });
        */
        inputField.fileinput(options).on('fileuploaded', function(event, data, id, index){
            console.log('fileuploaded');
            console.log(data.response);
            console.log(id);
            console.log(index);
        });
    };

    /**
     * Creates file input from existings files.
     *
     * @param  jQueryObject inputField to build the library on
     */
    FileInput.prototype.createFromExisting = function (files, inputField) {
        var that = this;
        var existing = {
            initialPreview: this.options.initialPreview,
            initialPreviewConfig: this.options.initialPreviewConfig,
            initialPreviewAsData: true,
            //Keep existing images on adding new images.
            overwriteInitial: false,
            ajaxDeleteSettings: {
                type: 'delete',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
            }
        };
        var options = $.extend({}, this.defaults(), existing);
        inputField.fileinput(options);
    };


    $("input[type=file]").each(function () {
        var files = $(this).data('files');
        var name = $(this).attr('name');
        var fi = new FileInput(files, name, $(this));
    });
});
