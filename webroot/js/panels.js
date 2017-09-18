(function ($) {
    'use strict';

    var Panel = function (form) {
        this.form = form;
        this.url = $(form).data('panels-url');
        if (!this.form || !this.url) {
            return false;
        }

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
    Panel.prototype.isEligible = function () {
        var $form = this.form;

        return $form.has('[data-provide="dynamic-panel"]').length ? true : false;
    };

    Panel.prototype.buildData = function () {
        var $form = this.form;
        var data = {};
        $form.find(':input').each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (typeof name !== 'undefined' && typeof value !== 'undefined' ) {
                data[name] = value;
            }
        });

        return data;
    };

    Panel.prototype.hidePanels = function (panels) {
        var $form = this.form;
        if (typeof panels === 'undefined' || !panels instanceof Array) {
            return false;
        }

        panels.forEach(function (cur) {
            var current = cur;
            var $panel = $form.find('[data-provide="dynamic-panel"]');
            $panel.each(function () {
                var title = $(this).find('[data-title="dynamic-panel-title"]').text();
                if (current === title) {
                    if (!$(this).hasClass('hidden')) {
                        $(this).addClass('hidden');
                        $(this).find(':input').attr('disabled', true);
                    }
                }
            });
        });
    };

    Panel.prototype.resetPanels = function () {
        var $form = this.form;
        $form.find('[data-provide="dynamic-panel"].hidden').find(':input').attr('disabled', false);
        $form.find('[data-provide="dynamic-panel"]').removeClass('hidden');
    };

    Panel.prototype.observe = function () {
        var $form = this.form;
        var that = this;
        $form.find(':input').change(function () {
            that.evaluateWithServer();
        });
    };

    Panel.prototype.evaluateWithServer = function () {
        var $form = this.form;
        var token = api_options.token;
        var that = this;
        $.ajax({
            url: that.url,
            type: 'POST',
            data: that.buildData(),
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function (data) {
                if (data.success) {
                    that.resetPanels();
                    that.hidePanels(data.data['fail']);
                }
            },
        });
    };


    $('form').each(function (i) {
        new Panel($(this));
    });

})(jQuery);
