var csv_migrations_select2 = csv_migrations_select2 || {};

(function ($) {
    /**
     * Select2 Logic.
     */
    function Select2()
    {
        //
    }

    /**
     * Setup method.
     *
     * @return {undefined}
     */
    Select2.prototype.setup = function (options) {
        this.min_length = options.hasOwnProperty('min_length') ? options.min_length : 1;
        this.timeout = options.hasOwnProperty('timeout') ? options.timeout : 300;
        this.api_token = options.hasOwnProperty('token') ? options.token : null;
        this.limit = options.hasOwnProperty('limit') ? options.limit : 10;
        this.id = options.hasOwnProperty('id') ? options.id : null;

        var that = this;
        // loop through select2 inputs
        $(this.id).each(function () {
            that.init(this);
        });

        // call observe method
        this._observe();
    };

    /**
     * Initialize method.
     *
     * @return {undefined}
     */
    Select2.prototype.init = function (element) {
        // set select2 option label, if is empty and the option value is set
        if ($(element).val() && !$(element).text()) {
            this._setDisplayValue($(element).val(), element);
        }

        // enable select2 functionality
        this._enable(element);
    };

    /**
     * Observe for select2 inputs added to the DOM client side.
     *
     * @return {undefined}
     */
    Select2.prototype._observe = function () {
        var that = this;

        MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        // observe for client side appended select2 elements
        var observer = new MutationObserver(function (mutations, observer) {
            // look through all mutations that just occured
            mutationsLength = mutations.length;
            for (var i = 0; i < mutationsLength; ++i) {
                // look through all added nodes of this mutation
                mutationNodesLength = mutations[i].addedNodes.length;
                for (var j = 0; j < mutationNodesLength; ++j) {
                    // look for select2 elements
                    var select2 = that._getSelect2(mutations[i].addedNodes[j]);
                    if ($.isEmptyObject(select2)) {
                        continue;
                    }

                    that.init(select2);
                }
            }
        });

        // define what element should be observed by the observer
        // and what types of mutations trigger the callback
        observer.observe(document, {
            childList: true,
            subtree: true
        });
    };

    /**
     * Find out if added node is a select2 element
     * and return it if it is, otherwise return empty object.
     *
     * @param  {object} node Added DOM node
     * @return {object}
     */
    Select2.prototype._getSelect2 = function (node) {
        var result = {};
        $(node).find(this.id).each(function () {
            result = this;
        });

        return result;
    };

    /**
     * Method that enables select2 functionality on specified input.
     *
     * @param {string} input select2 input
     * @return {undefined}
     * {@link plugin: https://select2.github.io}
     */
    Select2.prototype._enable = function (input) {
        var that = this;
        var placeholder = $(input).attr('title');
        // enable select2
        $(input).select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: placeholder,
            minimumInputLength: that.min_length,
            ajax: {
                url: $(input).data('url'),
                dataType: 'json',
                contentType: 'application/json',
                accepts: {
                    json: 'application/json'
                },
                delay: that.timeout,
                method: 'get',
                headers: {
                    'Authorization': 'Bearer ' + that.api_token
                },
                data: function (params) {
                    return {
                        query: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    var result = [];
                    $.each(data.data, function (k, v) {
                        result.push({
                            id: k,
                            name: v
                        });
                    });
                    params.page = params.page || 1;

                    return {
                        results: result,
                        pagination: {
                            more: (params.page * 10) < data.pagination.count
                        }
                    };
                },
                cache: true
            },
            templateResult: function (data) {
                if (data.loading) {
                    // don't show any text if minimum input legth is 0
                    if (0 === that.min_length) {
                        return;
                    }

                    return data.text;
                }

                return data.name;
            },
            templateSelection: function (data) {
                return data.name || data.text;
            }
        });
    };

    /**
     * Set and set select2 field label value, based on table's display field.
     *
     * @param {string} id Record id
     * @param {object} input Select2 input
     * @return {undefined}
     */
    Select2.prototype._setDisplayValue = function (id, input) {
        var that = this;
        var url = $(input).data('url').replace('/lookup', '/view/' + id);
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + that.api_token
            },
            success: function (data) {
                if (!data.success) {
                    return;
                }
                var label = data.data[$(input).data('display-field')];
                $(input).find(':selected').remove();
                var option = $('<option>' + label + '</option>').val(id);
                $(input).append(option).trigger('change');
                // set select2 display value
                $(input).trigger('change');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    csv_migrations_select2 = new Select2();

})(jQuery);
