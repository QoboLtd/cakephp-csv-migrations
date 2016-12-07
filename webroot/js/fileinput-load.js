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
    uploadUrl: '/api/file-storages/upload'
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
    var config =  {};
    this.options.initialPreviewConfig = [];

    if (files) {
      files.forEach( function(file, index) {
        //assembling an object of options
        var options = $.extend(config, {
          key: index,
          url: fileInputOptions.initialPreviewConfig.url + file.id
        });

        that.options.initialPreviewConfig.push(options);
      });
    }
  };

  FileInput.prototype.initialPreview = function (files) {
    var that = this;
    this.options.initialPreview = [];

    if (files) {
      files.forEach( function(file) {
        that.options.initialPreview.push(file.path);
      });
    }
  };

  /**
   * Creates new instance of fileinput.
   *
   * @param  jQueryObject inputField to build the library on
   */
  FileInput.prototype.createNew = function (inputField) {
    var options = $.extend({}, this.defaults());

    inputField.fileinput(options).on("filebatchselected", function(event){
      //@NOTE: updateFiles is used in embedded.js
      $(document).trigger('updateFiles', [event.target.files, $(this).attr('name')]);
    });

    inputField.fileinput(options).on('fileuploaded', function(event, data){
      if (data.response.id !== undefined) {
        if (data.response.id.length) {
          var fieldNameParts = $(this).attr('name').match(/^(\w+)\[(\w+)\]/);
          var found = false;

          if (fieldNameParts.length) {
            var elementId = `.${fieldNameParts[1].toLowerCase()}_${fieldNameParts[2].toLowerCase()}_ids`;
            var hiddenElements = $.find(elementId);
            var firstElement = $(hiddenElements).first();

            hiddenElements.forEach( function(input) {
              if ($(input).val() === data.response.id) {
                found = true;
              }
            });

            if (!found) {
              var clonedHiddenInput = $(firstElement).clone().val(data.response.id);
              $(clonedHiddenInput).appendTo($(elementId).parent());
            }
          }
        }
      }
    });
  };

  /**
   * Creates file input from existings files.
   *
   * @param  jQueryObject inputField to build the library on
   */
  FileInput.prototype.createFromExisting = function (inputField, files) {
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

    var options = $.extend({}, this.defaults(), existing);
    inputField.fileinput(options);
  };

  /* initializing the plugin */
  $("input[type=file]").each(function () {
    var files = $(this).data('files');
    var name = $(this).attr('name');

    new FileInput(files, name, $(this));
  });

});
