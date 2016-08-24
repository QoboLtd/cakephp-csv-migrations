(function($) {
    'use strict'

    var Panel = function() {
        this.setForm();
        this.observe();
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
                    }
                }
            });
        });
    };

    Panel.prototype.resetVisibility = function() {
        $('.panel').removeClass('hidden');
    };

    Panel.prototype.setForm = function() {
        this.form = $('.panel').closest('form');
    };

    Panel.prototype.observe = function() {
        var that = this;
        var $form = $('form');
        var url = $form.attr('action');
        var token = api_options.token;
        $form.find(':input').change(function() {
            $.ajax({
                url: '/api/contacts/panels/',
                type: 'POST',
                data: that.buildData(),
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(data)
                {
                    if(typeof data.error === 'undefined') {
                        that.resetVisibility();
                        that.hidePanels(data.data);
                    } else {
                        console.log('fail');
                    }
                },
            });
        });
    };

    new Panel();


})(jQuery);
