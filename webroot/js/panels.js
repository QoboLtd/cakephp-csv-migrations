(function($) {
    'use strict'

    var Panel = function() {
        this.observe();
    };

    Panel.prototype.buildData = function() {
        var $form = $('form');
        var data = {};
        $form.find(':input').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (typeof name !== 'undefined' && typeof value !== 'undefined' ) {
                data[name] = value;
            }
        });
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
                cache: false,
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(data)
                {
                    if(typeof data.error === 'undefined') {
                        console.log('success');
                    } else {
                        console.log('fail');
                    }
                },
            });
        });
    };

    new Panel();


})(jQuery);
