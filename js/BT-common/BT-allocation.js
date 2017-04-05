var bt_allocation_config = {
    totalVariants: 1,
    ctrlAllocation: 1,
    defaultVal: 100,
    maxValue: 100,
    inputFields: [],
    // Adds the jquery UI animation to the percentage sliders
    initPercentageSliders: function () {
        var self = this;
        this.totalVariants = 1;
        this.ctrlAllocation = BTeditorVars.ctrlAllocation;
        var diff = false;

        $('#percentage_sliders .ui-slider').each(function () {
            $(this).slider('destroy');
        });
        $('.slider_element').not('.slider_original').remove();

        $('.slider_bar').filter('.slider_original').attr('data-allocation', this.ctrlAllocation);

        $.each(BTVariantsData.pages, function (ind, page) {
            $.each(page.variants, function (vIndex, variant) {
                self.totalVariants++;
                var vname = variant.name;
                diff = diff || (variant.allocation - self.ctrlAllocation > 0.0001 || self.ctrlAllocation - variant.allocation > 0.0001);

                var sliderLabel = $('#percentage_sliders').find('.slider_label').first().clone().appendTo('#percentage_sliders');
                sliderLabel.removeClass('slider_original');
                sliderLabel.find('.slider_vname').html(vname + ':');

                var sliderBar = $('#percentage_sliders').find('.slider_bar').first().clone().appendTo('#percentage_sliders');
                sliderBar.removeClass('slider_original');
                sliderBar.attr('data-allocation', variant.allocation);
                sliderBar.find('.slider').attr('data-variant', vIndex);

                var inp = $('.allocation_percent_content').first().clone().appendTo('#allocation_percent_container');
                inp.removeClass('slider_original');
                inp.find('.allocation_percent_value').attr('data-variant', vIndex);
                inp.find('.allocation_variantid').val(variant.id);
            });
            return false;
        });

        var def = 100 / this.totalVariants;
        this.defaultVal = Math.round(def * 100) / 100;

        if (diff) {
            $('.slider_bar').each(function (ind) {
                var alloc = parseFloat($(this).attr('data-allocation')) * 100;
                var v = Math.round(alloc * 100) / 100;
                $(this).find('.slider').html(v);
                $('.allocation_percent_value').filter(':eq(' + ind + ')').val(v);
            });
        } else {
            $('.slider_bar').each(function (ind) {
                $(this).find('.slider').html(self.defaultVal);
                $('.allocation_percent_value').filter(':eq(' + ind + ')').val(self.defaultVal);
            });
        }

        setTimeout(function () {
            self.addSliderPlugin();
        }, 9);
    },
    // Sets the initial values and binds the "slide" action
    addSliderPlugin: function () {
        var self = this;
        var sliders = $('#percentage_sliders .slider');

        sliders.each(function () {
            var init_value = parseFloat($(this).text());

            self.setInputFieldAllocation($(this), init_value);

            $(this).empty().slider({
                value: init_value,
                min: 0,
                max: self.maxValue,
                range: 'max',
                step: 1,
                animate: 99,
                slide: function (event, ui) {
                    self.slideChange($(this), ui.value);
                }
            });
        });

        OpenPopup("#allocation_popup");
        this.bindPercentageActions();
    },
    // When sliding the ui or changing the input value.
    slideChange: function ($elem, value, fix) {
        var self = this;
        var val = parseFloat(value);
        var total = 0;
        var $sliders = $('#percentage_sliders .slider');

        this.setInputFieldAllocation($elem, val);
        this.setEditedElementClass($sliders, $elem, val);

        $sliders.not($elem).each(function () {
            total += $(this).slider('option', 'value');
        });

        total += val;

        var delta = this.maxValue - total;
        var allValues = 0;
        var divisor = $sliders.not('.manually_set').not('.zero_set').length;

        // Updates all other sliders depending on the current slider value
        $sliders.not('.manually_set').not('.zero_set').each(function (i) {
            var value = $(this).slider('option', 'value');

            if (divisor < 1) {
                divisor = 1;
            }

            var new_value = value + (delta / divisor);
            new_value = Math.floor(new_value * 100) / 100;

            if (new_value <= 0 || val === 100) {
                new_value = 0;
                $(this).addClass('zero_set');
            }

            if (new_value > 100) {
                new_value = 100;
            }
            allValues += new_value;

            self.setInputFieldAllocation($(this), new_value);

            $(this).slider('value', new_value);
        });
    },
    // Sets or unsets the HTML classes for the bahavior control of the given elements
    setEditedElementClass: function ($sliders, $elem, val) {
        var unsetValue = 0;
        var notZeroValue = 0;

        $elem.removeClass('zero_set');

        if (!$elem.hasClass('manually_set')) {
            $elem.addClass('manually_set');
        }

        if (val <= 0) {
            $elem.addClass('zero_set');
        }

        if (this.totalVariants - $sliders.filter('.zero_set').length <= 1) {
            $sliders.removeClass('zero_set');
        }

        $sliders.not('.manually_set').each(function () {
            unsetValue += $(this).slider('option', 'value');
        });

        $sliders.not('.zero_set').each(function () {
            notZeroValue += $(this).slider('option', 'value');
        });

        var resetManualSliders = this.totalVariants - $sliders.filter('.manually_set').length < 1;
        resetManualSliders |= unsetValue <= 1;
        resetManualSliders |= (notZeroValue + val) < this.maxValue;

        if (resetManualSliders) {
            $sliders.not($elem).removeClass('manually_set');
        }
    },
    // Based on the slider value, sets the corresponding input value
    setInputFieldAllocation: function ($elem, value) {
        var $input = $('.allocation_percent_value').first();
        var vIndex = $elem.attr('data-variant') || false;

        if (vIndex) {
            $input = $('#allocation_percent_container').find('.allocation_percent_value[data-variant=' + vIndex + ']');
        }

        $input.val(value);
    },
    // Binds the click on "reset distribution" or custom % input values
    bindPercentageActions: function () {
        var self = this;
        var tout = false;

        $('.allocation_reset').off('click').on('click', function () {
            $('#percentage_sliders .slider').each(function () {
                $(this).slider('value', self.defaultVal);
            });
            $('.allocation_percent_value').each(function (ind) {
                $(this).val(self.defaultVal);
            });
        });

        $('.allocation_percent_value').off('keydown').on('keydown', function (e) {
            var evt = e ? e : window.event;
            var charCode = (evt.which) ? parseInt(evt.which) : parseInt(evt.keyCode);
            var validKey = charCode === 8 ||
                    charCode === 9 ||
                    (charCode <= 57 && charCode >= 48) ||
                    (charCode <= 105 && charCode >= 96);


            if (charCode === 110 || charCode === 190) {
                return true;
            } else if (!validKey) {
                return false;
            }

            clearTimeout(tout);
            var $input = $(this);

            tout = setTimeout(function () {
                var vIndex = $input.attr('data-variant') || false;
                var newVal = $input.val().replace(/[\-]+/g, '');

                if (newVal === '') {
                    newVal = 0;
                } else if (newVal > 100) {
                    newVal = 100;
                }

                $input.val(newVal);

                var $slider = $('#percentage_sliders').find('.slider').first();
                if (vIndex) {
                    $slider = $('#percentage_sliders').find('.slider[data-variant=' + vIndex + ']');
                }

                $slider.slider('value', newVal);
                self.slideChange($slider, newVal);
            }, 99);
        });
    },
    // When submitting a form or going one step back
    bindFormActions: function () {
        var self = this;
        $('.action_menu_allocation').off('click').on('click', function () {
            self.initPercentageSliders();
        });

        $('.wizard_step_allocation').find('.editor_back').off('click').on('click', function () {
            $.fancybox.close();
        });

        $('.wizard_step_allocation').off('submit').on('submit', function () {
            var controller = BTTestType === 'split' ? 'lpc/save/' : 'ue/ls/';

            $.ajax({
                type: "POST",
                url: BTeditorVars.BaseSslUrl + controller + BTeditorVars.ClientId,
                data: $("#frmAllocation").serialize()
            }).done(function (res) {
                $.fancybox.close();
                location.reload();
            }).fail(function () {
                console.log('Error connecting with the server');
            });
        });
    },
    init: function () {
        this.bindFormActions();
    }
};

$(document).on('ready', function () {
    bt_allocation_config.init();
});