(function($) {
    'use strict'

    var Panel = function(form) {
        this.form = form;
        //Eligible form
        if (!this.isEligible()) {
            return false;
        }
        //run an initial evaluation with current form's settings.
        this.evaluateWithServer();
        //Observe the form.
        this.observe();
    };

    /**
     * Eligible forms contain panels.
     *
     * @return {Boolean} True if there is/are panel(s).
     */
    Panel.prototype.isEligible = function() {
        var $form = this.form;
        return $form.has('.panel').length ? true : false;
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


    $('form').find(':input').focus(function (){
        var $form = $(this).closest('form');
        new Panel($form);
    });

})(jQuery);
