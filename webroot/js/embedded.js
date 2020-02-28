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

        $.ajax({
            url: $(form).attr('action'),
            type: 'post',
            data: JSON.stringify(this.serializeObject(form)),
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data, textStatus, jqXHR) {
                // set related field display-field and value
                that._setRelatedField(data.data.id, form);

                // reset embedded form
                $(form)[0].reset();

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
     * Serialize Form to JSON.
     *
     * @param {string} form_id Form identifier
     * @return {object}
     * @link https://css-tricks.com/snippets/jquery/serialize-form-to-json
     */
    Embedded.prototype.serializeObject = function (form_id) {
        var data = {};

        $.each($(form_id).serializeArray(), function () {
            var matches = this.name.match(/\[(.*?)\]/);
            this.name = matches ? matches[1] : this.name;

            if (data[this.name]) {
                if (! data[this.name].push) {
                    data[this.name] = [data[this.name]];
                }
                data[this.name].push(this.value || '');
            } else {
                data[this.name] = this.value || '';
            }
        });

        return data;
    }

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
            success: function (response, textStatus, jqXHR) {
                that._setRelatedField(id, form)
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

    $(document).ready(function () {
        embedded = new Embedded([]);

        embedded.init();
    });

})(jQuery);
