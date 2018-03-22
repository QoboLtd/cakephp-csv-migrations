var embedded = embedded || {};

(function ($) {
    /**
     * Embedded Logic.
     * @param {object} options configuration options
     */
    function Embedded(options)
    {
        this.files = null;
        this.uploadFieldName = null;
        this.formId = 'form[data-embedded="1"]';
        this.api_token = api_options.hasOwnProperty('token') ? api_options.token : null;
        this.attachEvents();
    }

    /**
     * Initialize method.
     *
     * @return {void}
     */
    Embedded.prototype.init = function () {
        var that = this;

        $(that.formId).submit(function (e) {
            e.preventDefault();
            that._submitForm(this);
        });
    };

    /**
     * Attach events needed for the embedded form.
     * @return void
     */
    Embedded.prototype.attachEvents = function () {
        var that = this;
        $(document).on('updateFiles', function (event, files, fieldName) {
            that.files = files;
            that.uploadFieldName = fieldName;
        });
    };

    /**
     * Method that handles form submission.
     *
     * @param  {object} form Form element
     * @return {void}
     * @todo display form errors
     */
    Embedded.prototype._submitForm = function (form) {
        var that = this;
        var withRelate = $(form).data('embedded-related-model') && $(form).data('embedded-related-id');

        var url = $(form).attr('action');
        var embedded = $(form).data('embedded');
        var data = {};
        var related = {};

        $.each($(form).serializeArray(), function (i, field) {
            if (0 === field.name.indexOf(embedded)) {
                var name = field.name.replace(embedded, '');

                name = name.replace('[', '');
                name = name.replace(']', '');

                // @NOTE: if the field name is an array,
                // we push multiple values.
                // Example: file_ids[] - we push all the values in.
                if (name.match(/\[(\d+)?\]$/)) {
                    name = name.replace('[', '');
                    name = name.replace(']', '');

                    if (data[name] === undefined) {
                        data[name] = [];
                    } else {
                        if (!data[name].includes(field.value)) {
                            data[name].push(field.value);
                        }
                    }
                } else {
                    data[name] = field.value;
                }
            } else {
                if (field.name.match(/related_(id|model)/)) {
                    related[field.name] = field.value;
                }
            }
        });
        data = JSON.stringify(data);
        $.ajax({
            url: $(form).attr('action'),
            type: 'post',
            data: data,
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                // set related field display-field and value
                withRelate ? that._setRelations(data.data.id, form) : that._setRelatedField(data.data.id, form);

                /*
                clear embedded form
                 */
                that._resetForm(form);

                // hide modal
                $($(form).closest('.modal')).modal('hide');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    /**
     * Set value and display field for related field after successful form submission.
     *
     * @param {string} id record id
     * @param {object} form Form element
     * @return {void}
     */
    Embedded.prototype._setRelations = function (id, form) {
        var that = this;

        var associationName = $(form).data('embedded-association-name');
        var relatedModel = $(form).data('embedded-related-model');
        var relatedId = $(form).data('embedded-related-id');
        var data = {[associationName] : {'_ids' : [id]}};

        $.ajax({
            url: '/' + relatedModel + '/link/' + relatedId + '/' + associationName,
            type: 'post',
            dataType: 'json',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    /**
     * Set value and display field for related field after successful form submission.
     *
     * @param {string} id record id
     * @param {object} form Form element
     * @return {void}
     */
    Embedded.prototype._setRelatedField = function (id, form) {
        var that = this;
        var url = $(form).attr('action').replace('/add', '/view/' + id + '.json');

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                // get select2 field
                var label = data.data[$(form).data('embedded-display-field')];
                var field = $('#' + $(form).data('embedded-field-id'));
                var option = $('<option selected>' + label + '</option>').val(id);
                field.append(option).trigger('change');
            },
            error: function (jqXHR, textStatus, errorThrown) {
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
    Embedded.prototype._resetForm = function (form) {
        $(form).find('input:text, input:password, input:file, select, textarea').val('');
        $(form).find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
    };

    embedded = new Embedded([]);

    embedded.init();

})(jQuery);
