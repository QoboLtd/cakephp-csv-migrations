var embedded = embedded || {};

(function($) {
    /**
     * Embedded Logic.
     * @param {object} options configuration options
     */
    function Embedded(options) {
        this.files = null;
        this.uploadFieldName = null;
        this.formId = options.hasOwnProperty('formId') ? options.formId : '.embeddedForm';
        this.attachEvents();
    }

    /**
     * Initialize method.
     *
     * @return {void}
     */
    Embedded.prototype.init = function() {
        var that = this;

        $(that.formId).submit(function(e) {
            e.preventDefault();
            if (that.files && that.uploadFieldName) {
                that.uploadFiles(this);
            } else {
               that._submitForm(this);
            }
        });
    };

    /**
     * Attach events needed for the embedded form.
     * @return void
     */
    Embedded.prototype.attachEvents = function() {
        var that = this;
        $(document).on('updateFiles', function(event, files, fieldName) {
            that.files = files;
            that.uploadFieldName = fieldName;
        });
    };

    Embedded.prototype.uploadFiles = function(form) {
        var that = this;
        var data = new FormData();
        var modalId = $(form).data('modal_id');
        var url = $(form).attr('action');

        $.each(that.files, function(key, value)
        {
            data.append('file[]', value);
        });

        if (that.uploadFieldName) {
            data.append('fieldName', that.uploadFieldName);
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR)
            {
                if(typeof data.error === 'undefined')
                {
                    /*
                    set related field display-field and value
                     */
                    that._setRelatedField(url, data.data.id, form);

                    /*
                    clear embedded form
                     */
                    that._resetForm(form);

                    /*
                    hide modal
                     */
                    $('#' + modalId).modal('hide');
                }
                else
                {
                    // Handle errors here
                    console.log('ERRORS: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                console.log('ERRORS: ' + textStatus);
                // STOP LOADING SPINNER
            }
        });
    };

    /**
     * Method that handles form submission.
     *
     * @param  {object} form Form element
     * @return {void}
     * @todo display form errors
     */
    Embedded.prototype._submitForm = function(form) {
        var that = this;

        var url = $(form).attr('action');
        var embedded = $(form).data('embedded');
        var modalId = $(form).data('modal_id');
        var data = {};
        $.each($(form).serializeArray(), function(i, field) {
            if (0 === field.name.indexOf(embedded)) {
                var name = field.name.replace(embedded, '');
                name = name.replace('[', '');
                name = name.replace(']', '');
                data[name] = field.value;
            }
        });
        data = JSON.stringify(data);

        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            contentType: 'application/json',
            success: function(data, textStatus, jqXHR) {
                /*
                set related field display-field and value
                 */
                that._setRelatedField(url, data.data.id, form);

                /*
                clear embedded form
                 */
                that._resetForm(form);

                /*
                hide modal
                 */
                $('#' + modalId).modal('hide');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    /**
     * Set value and display field for related field after successful form submission.
     *
     * @param {string} url  ajax url
     * @param {string} id   record id
     * @param {object} form Form element
     * @return {void}
     */
    Embedded.prototype._setRelatedField = function(url, id, form) {
        url = url.replace('/add', '/' + id + '.json');
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            contentType: 'application/json',
            success: function(data, textStatus, jqXHR) {
                /*
                get typeahead label field
                 */
                $labelField = $('#' + $(form).data('field_id'));

                displayField = $(form).data('display_field');

                /*
                set typeahead value
                 */
                $labelField.val(data.data[displayField]);

                /*
                set typeahead as read-only
                 */
                $labelField.prop('readonly', true);

                /*
                set typeahead hidden foreign_key value
                 */
                $('#' + $labelField.data('id')).val(id);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    /**
     * Clear embedded form on successful submission.
     *
     * @param  {object} form Form element
     * @return {void}
     */
    Embedded.prototype._resetForm = function(form) {
        $(form).find('input:text, input:password, input:file, select, textarea').val('');
        $(form).find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
    };

    embedded = new Embedded([]);

    embedded.init();

})(jQuery);
