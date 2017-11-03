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
        this.formId = options.hasOwnProperty('formId') ? options.formId : '.embeddedForm';
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

        var url = $(form).attr('action');
        var embedded = $(form).data('embedded');
        var modalId = $(form).data('modal_id');
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
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                /*
                set related field display-field and value
                 */
                if (related.related_model) {
                    that._setRelations(related, data.data.id, embedded.toLowerCase());
                } else {
                    that._setRelatedField(url, data.data.id, form);
                }

                /*
                clear embedded form
                 */
                that._resetForm(form);

                /*
                hide modal
                 */
                $('#' + modalId).modal('hide');
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
     * @param {array} related   related data
     * @param {string} id       record id
     * @param {string} model    model name
     * @return {void}
     */
    Embedded.prototype._setRelations = function (related, id, model) {
        var that = this;
        url = '/' + related.related_model + '/link/' + related.related_id;
        data = {
            assocName: model,
            id:related.related_id,
            [model] : {
                '_ids' : [
                    id
                ]
            }
        };
        data = JSON.stringify(data);
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                window.location.hash = '#' + model;
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
     * @param {string} url  ajax url
     * @param {string} id   record id
     * @param {object} form Form element
     * @return {void}
     */
    Embedded.prototype._setRelatedField = function (url, id, form) {
        var that = this;
        url = url.replace('/add', '/view/' + id + '.json');
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
                var label = data.data[$(form).data('display_field')];
                var field = $('#' + $(form).data('field_id'));
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
