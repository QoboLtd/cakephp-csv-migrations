;(function ($) {
    $('.pickr').each(function () {
        const pickr = new Pickr({
            el: this,
            useAsButton: true,
            "default": (this.value ? this.value : '#000000'),
            theme: 'classic',
            swatches: [
                'rgba(244, 67, 54, 1)',
                'rgba(233, 30, 99, 1)',
                'rgba(156, 39, 176, 1)',
                'rgba(103, 58, 183, 1)',
                'rgba(63, 81, 181, 1)',
                'rgba(33, 150, 243, 1)',
                'rgba(3, 169, 244, 1)',
                'rgba(0, 188, 212, 1)',
                'rgba(0, 150, 136, 1)',
                'rgba(76, 175, 80, 1)',
                'rgba(139, 195, 74, 1)',
                'rgba(205, 220, 57, 1)',
                'rgba(255, 235, 59, 1)',
                'rgba(255, 193, 7, 1)'
            ],

            components: {
                preview: true,
                opacity: false,
                hue: true,

                interaction: {
                    input: true,
                    clear: true,
                    save: true
                }
            }
        })
            .on('init', pickr => {
                this.value =
                    pickr.getSelectedColor() == null
                        ? '#00000'
                        : pickr
                              .getSelectedColor()
                              .toHEXA()
                              .toString(0)

                $(this)
                    .parent()
                    .find('i')
                    .css('background-color', this.value)
            })
            .on('show', pickr => {
                this.value =
                    pickr.getSelectedColor() == null
                        ? '#00000'
                        : pickr
                              .getSelectedColor()
                              .toHEXA()
                              .toString(0)
            })
            .on('save', color => {
                this.value = color == null ? '' : color.toHEXA().toString(0)
                $(this)
                    .parent()
                    .find('i')
                    .css('background-color', this.value)
                pickr.hide()
            })
    })
})(jQuery)
