var typeahead = typeahead || {};

(function ($) {
    /**
     * Typeahead Logic.
     *
     * @param {object} options configuration options
     */
    function Typeahead(options)
    {
        this.min_length = options.hasOwnProperty('min_length') ? options.min_length : 1;
        this.timeout = options.hasOwnProperty('timeout') ? options.timeout : 300;
        this.api_token = options.hasOwnProperty('token') ? options.token : null;
        this.typeahead_id = '[data-type="typeahead"]';

        var that = this;
        // loop through typeahead inputs
        $(this.typeahead_id).each(function () {
            that.init(this);
        });

        // call observe method
        this._observe();
    }

    /**
     * Initialize method.
     *
     * @return {void}
     */
    Typeahead.prototype.init = function (element) {
        var that = this;
        var hidden_input = $('#' + $(element).data('id'));

        // set typeahead display field if is empty and the hidden input value is set
        if (hidden_input.val() && !$(element).val()) {
            this._setDisplayValue(hidden_input.val(), element);
        }
        // enable typeahead functionality
        this._enable(element, hidden_input);

        // clear inputs on double click
        $(element).on('dblclick', function () {
            that._clearInputs(this, hidden_input);
        });
    };

    /**
     * Set and set typeahead field label value, based on table's display field.
     *
     * @param {string} id    Record id
     * @param {object} input Typeahead input
     * @return {void}
     */
    Typeahead.prototype._setDisplayValue = function (id, input) {
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

                // set typeahead display value
                $(input).val(data.data[$(input).data('display-field')]);

                // set typeahead as read-only
                $(input).prop('readonly', true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    };

    /**
     * Observe for typeahead inputs added to the DOM client side.
     *
     * @return {void}
     */
    Typeahead.prototype._observe = function () {
        var that = this;

        MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        // observe for client side appended typeaheads
        var observer = new MutationObserver(function (mutations, observer) {
            // look through all mutations that just occured
            mutationsLength = mutations.length;
            for (var i = 0; i < mutationsLength; ++i) {
                // look through all added nodes of this mutation
                mutationNodesLength = mutations[1].addedNodes.length;
                for (var j = 0; j < mutationNodesLength; ++j) {
                    // look for typeahead elements
                    var typeahead = that._getTypeahead(mutations[i].addedNodes[j]);
                    if ($.isEmptyObject(typeahead)) {
                        continue;
                    }

                    that.init(typeahead);
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
     * Find out if added node is a typeahead element
     * and return it if it is, otherwise return empty object.
     *
     * @param  {object} node Added DOM node
     * @return {object}
     */
    Typeahead.prototype._getTypeahead = function (node) {
        var result = {};
        $(node).find(this.typeahead_id).each(function () {
            result = this;
        });

        return result;
    };

    /**
     * Method used for clearing typeahead inputs.
     *
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @return {void}
     */
    Typeahead.prototype._clearInputs = function (input, hidden_input) {
        if ($(input).is('[readonly]')) {
            $(input).prop('readonly', false);
            $(input).val('');
            $(hidden_input).val('');
        }
    };

    /**
     * Method that enables typeahead functionality on specified input.
     *
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @return {void}
     * {@link plugin: http://plugins.upbootstrap.com/bootstrap-ajax-typeahead/}
     */
    Typeahead.prototype._enable = function (input, hidden_input) {
        var that = this;

        // enable typeahead
        $(input).typeahead({
            // ajax
            ajax: {
                url: $(input).data('url'),
                timeout: that.timeout,
                triggerLength: that.min_length,
                method: 'get',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + that.api_token
                },
                preProcess: function (data) {
                    if (data.success === false) {
                        // Hide the list, there was some error
                        return false;
                    }
                    result = [];
                    $.each(data.data, function (k, v) {
                        result.push({
                            id: k,
                            name: v
                        });
                    });

                    return result;
                }
            },
            onSelect: function (data) {
                that._onSelect(input, hidden_input, data);
            },
            // No need to run matcher as ajax results are already filtered
            matcher: function (item) {
                return true;
            },
        });
    };

    /**
     * Method responsible for handling behavior on typeahead option selection.
     *
     * @param  {object} input        typeahead input
     * @param  {object} hidden_input hidden input, value holder
     * @param  {object} data         ajax call returned data
     * @return {void}
     */
    Typeahead.prototype._onSelect = function (input, hidden_input, data) {
        $(hidden_input).val(data.value);
        $(input).prop('readonly', true);
    };

    typeahead = new Typeahead(typeahead_options);

})(jQuery);
