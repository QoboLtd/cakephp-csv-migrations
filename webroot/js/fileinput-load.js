$(document).ready(function () {
    'use strict';

  /* constructor */
    var FileInput = function (files, name, field) {
        this.html = this.staticHtml;
        this.api_token = api_options.hasOwnProperty('token') ? api_options.token : null;
        this.options = {};

        if (typeof files === 'object') {
            this.initialPreview(files);
            this.initialPreviewConfig(files);
            this.createFromExisting(field, files);
        } else {
            this.createNew(field);
        }

        field.on('change', function (e) {
            //Trigger the updateFiles Event and pass all the collected uploads
            $(document).trigger('updateFiles', [e.target.files, $(this).attr('name')]);
        });
    };

    FileInput.prototype.defaultOptions = {
        uploadAsync: true,
        showUpload: true,
        showRemove: false,
        dropZoneEnabled: false,
        showUploadedThumbs: false,
        fileActionSettings: {
            showUpload: false,
            showZoom: false,
        },
        maxFileCount: 30,
        maxFileSize: 2000,
    };

    FileInput.prototype.staticHtml = {
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

  /**
   * Preview initial preview of the upload field.
   *
   * @param string url
   */
    FileInput.prototype.setInitialPreview = function (url) {
        var imgExtensions = /\.(jpeg|jpg|gif|png)$/;

        var initialPreview = (url.match(imgExtensions) !== null) ? this.html.img : this.html.previewOtherFile;

        this.preview = initialPreview.replace('%%url%%', url);

        return initialPreview;
    };

  /**
   * Builds the delete URL
   * @param string name of the field.
   */
    FileInput.prototype.setDeleteUrl = function (name) {
        var deleteUrl = '';
        var matches = name.match(/\[(\w+)\]\[(\w+)\]/);
        var fieldName = matches[2];

        deleteUrl = window.location.href.replace('edit', 'unlinkUpload') + '/' + fieldName;

        this.deleteUrl = deleteUrl;

        return deleteUrl;
    };

  /**
   * Plugin's default options.
   *
   * @return object Plugin's default options
   */
    FileInput.prototype.defaults = function () {
        var options = (fileInputOptions.defaults !== undefined) ? fileInputOptions.defaults : this.defaultOptions;

        return options;
    };

    FileInput.prototype.initialPreviewConfig = function (files) {
        var that = this;
        var filesOptions = [];

        this.options.initialPreviewConfig = {};

        if (files) {
            files.forEach(function (file) {
                if (file !== undefined) {
                    var options = {
                        key: file.id,
                        url: '/api/file-storages/delete/' + file.id
                    };
                    filesOptions.push(options);
                }
            });
        }

        that.options.initialPreviewConfig = filesOptions;

        return filesOptions;
    };

    FileInput.prototype.addDeleteUrls = function (ids) {
        var opts = [];

        if (ids.length) {
            ids.forEach(function (id) {
                opts.push({key: id, url: '/api/file-storages/delete/' + id});
            });
        }

        return opts;
    };

  /**
   * setting url for the preview content
   */
    FileInput.prototype.initialPreview = function (files) {
        var that = this;
        this.options.initialPreview = [];

        if (files) {
            files.forEach(function (file) {
                that.options.initialPreview.push(file.path);
            });
        }
    };

    FileInput.prototype.getHiddenField = function (inputField) {
        var result = false;
        var fieldNameParts = $(inputField).attr('name').match(/^(\w+)\[(\w+)\]/);

        if (fieldNameParts.length) {
            result = '.' + fieldNameParts[1] + '_' + fieldNameParts[2] + '_ids';
        }

        return result;
    };


  /**
   * Creates new instance of fileinput.
   *
   * @param  jQueryObject inputField to build the library on
   */
    FileInput.prototype.createNew = function (inputField) {
        var ids = [];
        var paths = [];
        var that = this;

        var existing = {
            overwriteInitial: false,
            initialPreviewAsData: true,
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

        if ($(inputField).attr('data-upload-url')) {
            options.uploadUrl = $(inputField).attr('data-upload-url');
        }

        inputField.fileinput(options).on("filebatchselected", function (event) {
            $(document).trigger('updateFiles', [event.target.files, $(this).attr('name')]);
        });

        inputField.fileinput(options).on('fileuploaded', function (event, data) {
            if (data.response.id !== undefined) {
                if (data.response.id.length) {
                    ids.push(data.response.id);
                    paths.push(data.response.path);
                    that.addHiddenFileId(this, data.response.id);
                }
            }
        }).on('filedeleted', function (event, key) {
            that.removeHiddenFileId(this, key);
        }).on('filebatchuploadcomplete', function (event) {
            var opts = that.addDeleteUrls(ids);
            options.initialPreviewConfig = opts;
            options.initialPreview = paths;
            that.refreshFileInput(this, options);
        });
    };

    FileInput.prototype.addHiddenFileId = function (inputField, id) {
        var added = false;
        var found = false;

        var elementId = this.getHiddenField(inputField);
        var hiddenFields = $.find(elementId);
        hiddenFields.forEach(function (element) {
            if ($(element).val() == id) {
                found = true;
            }
        });

        if (!found) {
            var clonedField = $(hiddenFields).first().clone().val(id);
            if (clonedField) {
                //appending new input element next to the others.
                $(clonedField).appendTo($(hiddenFields).first().parent());

                added = true;
            }
        }

        return added;
    };

  /**
   * removeHiddenFileId
   *
   * Removing input type='hidden' with corresponding file-storage id of the file
   * @param {Object} inputField of the actual file input element
   * @param {String} id UUID of the corresponding image file
   * @return {Boolean} removed if the input element was found and removed from DOM
   */
    FileInput.prototype.removeHiddenFileId = function (inputField, id) {
        var removed = false;
        var elementId = this.getHiddenField($(inputField));
        var hiddenFields = $.find(elementId);

        hiddenFields.forEach(function (element) {
            if ($(element).val() == id) {
                if (hiddenFields.length > 1) {
                    $(element).remove();
                } else {
                    $(element).val(null);
                }

                removed = true;
            }
        });

        return removed;
    };

  /**
   * Creates file input from existings files.
   *
   * @param  jQueryObject inputField to build the library on
   */
    FileInput.prototype.createFromExisting = function (inputField, files) {
        var ids = [];
        var paths = [];
        var that = this;

        // Keep existing images on adding new images,
        // overwrtting default options in case of existing files
        var existing = {
            overwriteInitial: false,
            initialPreview: this.options.initialPreview,
            initialPreviewConfig: this.options.initialPreviewConfig,
            initialPreviewAsData: true,
            ajaxDeleteSettings: {
                type: 'delete',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
            }
        };

        var defaultOptions = this.defaults();

        if ($(inputField).attr('data-upload-url')) {
            defaultOptions.uploadUrl = $(inputField).attr('data-upload-url');
        }

        var options = $.extend({}, defaultOptions, existing);

        inputField.fileinput(options).on('fileuploaded', function (event, data) {
            if (data.response.id !== undefined) {
                if (data.response.id.length) {
                    that.addHiddenFileId(this, data.response.id);
                }
            }
        }).on('filedeleted', function (event, key) {
            that.removeHiddenFileId(this, key);
        }).on('filebatchuploadcomplete', function (event) {
            var opts = that.addDeleteUrls(ids);
            options.initialPreviewConfig = opts;
            options.initialPreview = paths;
            that.refreshFileInput(this, options);
        });
    };

    FileInput.prototype.refreshFileInput = function (inputField, options) {
        $(inputField).fileinput('refresh', options);
    };

    $("input[type=file]").each(function () {
        var files = $(this).data('files');
        var name = $(this).attr('name');

        new FileInput(files, name, $(this));
    });

});
