if ('undefined' === typeof app.SearchFilter) {
    app.SearchFilter = {};
}

app.SearchFilter.Form = (function () {
    'use strict';

    var Form = function ($form) {
        this.$form = $form;

        this.setupFormSubmitHandler();
        this.setupDateRangePickers();
    };

    Form.prototype.setupFormSubmitHandler = function () {
        var that = this;

        // do not submit empty filter values (only submits filters which have values)
        this.$form.on('submit', function (e) {
            that.prepareFormSubmit();

            // no filters left -> load same URL without query string (no filters)
            if (that.$form.serializeArray().length === 0) {
                e.preventDefault();
                window.location = window.location.href.split('?')[0];
            }
        });
    };

    /**
     * Setup date range pickers and add hidden inputs with start/end values on change
     */
    Form.prototype.setupDateRangePickers = function () {
        var that = this;
        this.dateRangePickers = [];

        this.$form.find(':input.plugin-daterangepicker').each(function () {
            var $input = $(this);

            // handle update event and add hidden fields with from/to values to form
            $input.on('update.DateRangePicker', function (e, pickerResult) {
                var baseName = $input.attr('name').match(/^filter\[(.+)\]$/);
                if (null === baseName) {
                    app.Logger.error('Could not resolve base name for date range input', $input.attr('name'));
                    return;
                }

                baseName = baseName[1];

                $.each(['from', 'to'], function (index, type) {
                    var value = '';
                    if (pickerResult[type]) {
                        value = pickerResult[type].format('DD.MM.YYYY');
                    }

                    // orderDateFrom, orderDateTo
                    var inputName = baseName + type.charAt(0).toUpperCase() + type.slice(1)

                    that.updateHiddenField('filter[' + inputName + ']', value);
                });
            });

            // initialize date picker instance
            var dateRangePicker = app.Util.getOrCreateInstance(
                $(this), 'SearchFilter.DateRangePicker',
                function ($el) {
                    return new app.SearchFilter.DateRangePicker($el);
                }
            );

            that.dateRangePickers.push(dateRangePicker);
        });
    };

    /**
     * Add/update/remove hidden field from form
     *
     * @param name
     * @param value
     */
    Form.prototype.updateHiddenField = function (name, value) {
        var field = this.$form.find(':input[type="hidden"][name="' + name + '"]');
        value = $.trim(value);

        if (field.length === 0 && value.length > 0) {
            field = $('<input />')
                .attr('type', 'hidden')
                .attr('name', name);

            field.appendTo(this.$form);
        }

        // remove hidden field without value
        if (field.length === 1 && value.length === 0) {
            field.remove();
        } else {
            field.val(value);
        }
    };

    /**
     * Disable empty form fields before submitting form
     */
    Form.prototype.prepareFormSubmit = function () {
        var disableInput = function (input) {
            input.data('before-form-prepare-state', input.prop('disabled'));
            input.prop('disabled', true);
        };

        this.$form.find(':input').each(function () {
            var $input = $(this);
            if ($input.hasClass('form-submit-disabled') || !$.trim($input.val())) {
                disableInput($input);
            }
        });
    };

    /**
     * Restore form field state after submitting form
     */
    Form.prototype.restoreFormFieldStates = function () {
        this.$form.find(':input').each(function () {
            var $input = $(this);

            var previousState = !!$input.data('before-form-prepare-state');
            if ($input.prop('disabled')) {
                $input.prop('disabled', previousState);
            }
        });
    };

    return Form;
}());
