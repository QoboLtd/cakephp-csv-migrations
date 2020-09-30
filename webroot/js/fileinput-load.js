$(document).ready(function () {
    'use strict';

    /**
     * @TODO
     * Find a way to retrieve this from input.ctp
     */
    var previewTypes = {
        'application/pdf' : 'pdf',
        'application/msword' : 'object',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'object',
        'application/vnd.ms-excel' : 'object',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'object',
        'application/vnd.ms-powerpoint' : 'object',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' : 'object',
    };

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

    //Use this to override setting from config
    FileInput.prototype.defaultOptions = {};

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
                        url: '/api/file-storage/delete/' + file.id,
                        size: file.size,
                        caption: file.caption,
                        downloadUrl: file.path
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
            ids.forEach(function (element) {

                var tmpPreviewType = (previewTypes[element.filetype] ?? 'image');

                opts.push({
                    key: element.id,
                    url: '/api/file-storage/delete/' + element.id,
                    size: element.size,
                    caption: element.caption,
                    type: tmpPreviewType,
                    filetype: element.filetype,
                    downloadUrl: element.path,
                });
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

        //Enable file dragging based on data attribute data-file-order
        //set on FilesFieldHandler/input.ctp
        var showDrag = false;
        if (1 == $(inputField).attr('data-file-order')) {
            showDrag = true;
        }

        var maxFileCountAllowed = $(inputField).data('file-limit');

        var existing = {
            showUpload: false,
            showCaption: true,
            maxFileCount: maxFileCountAllowed,
            overwriteInitial: true,
            initialPreviewAsData: true,
            reversePreviewOrder: false,
            fileActionSettings: {
                showDrag: showDrag,
                showZoom: true,
                dragIcon: '<i class="glyphicon glyphicon-sort"></i>'
            },
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
            inputField.fileinput('upload');
        });

        inputField.fileinput(options).on('fileuploaded', function (event, data) {
            if (true === data.response.success) {

                var input = this;
                data.response.data.forEach(function (file) {
                    let tmp = {
                        id: file.id,
                        size: (file.hasOwnProperty('filesize') ? file.filesize : 0),
                        key: file.id,
                        caption: escape(file.filename),
                        filetype: file.mime_type
                    };
                    ids.push(tmp);
                    paths.push(file.path);
                    that.addHiddenFileId(input, file.id);
                });
            }
        }).on('filedeleted', function (event, key) {
            that.removeHiddenFileId(this, key);
        }).on('filebatchuploadcomplete', function (event) {
            var opts = that.addDeleteUrls(ids);
            options.initialPreviewConfig = opts;
            options.initialPreview = paths;
            that.refreshFileInput(this, options);
        }).on("filesorted", function (event, params) {
            $.post({
                url: '/api/file-storage/order',
                data: JSON.stringify(params.stack),
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
                dataType: 'json',
                contentType: 'application/json',
                success: function (data) {
                    if (data.success) {
                        $.notify(data.message, "success");
                    } else {
                        $.notify(data.message, "error");
                    }

                }
            });
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

        if (hiddenFields.length) {
            hiddenFields.forEach(function (element) {
                if ($(element).val() == '') {
                    $(element).remove();
                }
            });
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

        //Enable file dragging based on data attribute data-file-order and data-file-order-direction
        //set on FilesFieldHandler/input.ctp
        var showDrag = false;
        if (1 == $(inputField).attr('data-file-order')) {
            showDrag = true;
        }

        var maxFileCountAllowed = $(inputField).data('file-limit');
        // Keep existing images on adding new images,
        // overwrtting default options in case of existing files
        var existing = {
            showUpload: false,
            maxFileCount: maxFileCountAllowed,
            showCaption: true,
            overwriteInitial: false,
            fileActionSettings: {
                showDrag: showDrag,
                showZoom: true,
                dragIcon: '<i class="glyphicon glyphicon-sort"></i>'
            },
            initialPreview: this.options.initialPreview,
            initialPreviewConfig: this.options.initialPreviewConfig,
            initialPreviewAsData: true,
            reversePreviewOrder: false,
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
            if (true === data.response.success) {
                var input = this;
                data.response.data.forEach(function (file) {
                    that.addHiddenFileId(input, file.id);
                });
            }
        }).on('filedeleted', function (event, key) {
            that.removeHiddenFileId(this, key);
        }).on('filebatchuploadcomplete', function (event) {
            var opts = that.addDeleteUrls(ids);
            options.initialPreviewConfig = opts;
            options.initialPreview = paths;
        }).on("filebatchselected", function (event) {
            $(document).trigger('updateFiles', [event.target.files, $(this).attr('name')]);
            inputField.fileinput('upload');
        }).on("filesorted", function (event, params) {
            $.post({
                url: '/api/file-storage/order',
                data: JSON.stringify(params.stack),
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
                dataType: 'json',
                contentType: 'application/json',
                success: function (data) {
                    if (data.success) {
                        $.notify(data.message, "success");
                    } else {
                        $.notify(data.message, "error");
                    }

                }
            });
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
