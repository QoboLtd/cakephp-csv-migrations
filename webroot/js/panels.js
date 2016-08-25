(function($) {
    'use strict'

    var Panel = function() {
        if (!this.isEligibleAction()) {
            return false;
        }
        //set the monitoring form.
        this.setForm();
        //run an initial evaluation with current form's settings.
        this.evaluateWithServer();
        //Observe the form.
        this.observe();
    };

    /**
     * Check if the running action is eligible.
     *
     * This function provides the eligible actions for hiding and
     * showing the panels.
     * @return {Boolean} True if the current is action is eligible.
     */
    Panel.prototype.isEligibleAction = function() {
        var actions = ['add', 'edit'];
        var pathname = window.location.pathname;
        var matches = pathname.split('/', 3);
        var action = matches[matches.length - 1];
        var pos = actions.indexOf(action);

        return (pos > -1) ? true : false;
    };

    Panel.prototype.buildData = function() {
        var $form = this.form;
        var data = {};
        $form.find(':input').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (typeof name !== 'undefined' && typeof value !== 'undefined' ) {
                data[name] = value;
            }
        });

        return data;
    };

    Panel.prototype.hidePanels = function(panels) {
        if (!panels instanceof Array) {
            return false;
        }

        panels.forEach(function(cur){
            var current = cur;
            var $panel = $('.panel');
            $panel.each(function() {
                var title = $(this).find('.panel-title').text();
                if (current === title) {
                    if (!$(this).hasClass('hidden')) {
                        $(this).addClass('hidden');
                        $(this).find(':input').attr('disabled', true);
                    }
                }
            });
        });
    };

    Panel.prototype.resetPanels = function() {
        $('.panel.hidden').find(':input').attr('disabled', false);
        $('.panel').removeClass('hidden');
    };

    Panel.prototype.setForm = function() {
        this.form = $('.panel').closest('form');
    };

    Panel.prototype.observe = function() {
        var $form = this.form;
        var that = this;
        $form.find(':input').change(function() {
            that.evaluateWithServer();
        });
    };

    Panel.prototype.evaluateWithServer = function() {
        var $form = this.form;
        var action = $form.attr('action');
        var matches = action.split('/', 2);
        var module = matches[1];
        var that = this;
        if (!module) {
            return false;
        }
        var url = '/api/' + module + '/panels/';
        var token = api_options.token;
        $.ajax({
            url: url,
            type: 'POST',
            data: that.buildData(),
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(data)
            {
                if(typeof data.error === 'undefined') {
                    that.resetPanels();
                    that.hidePanels(data.data);
                } else {
                    console.log('Panel - Ajax failing. Unable to hide panels.');
                }
            },
        });
    };

    new Panel();


})(jQuery);
