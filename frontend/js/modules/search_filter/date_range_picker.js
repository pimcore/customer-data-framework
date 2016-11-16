app.SearchFilter.DateRangePicker = (function () {
    'use strict';

    var DateRangePicker = function ($input) {
        this.$input = $input;

        // do not include input display value in form submit
        $input.addClass('form-submit-disabled');

        this.initDateRangePicker();
    };

    DateRangePicker.prototype.triggerUpdate = function () {
        var picker = this.$input.data('daterangepicker');

        this.$input.trigger('update.DateRangePicker', {
            from: picker.startDate,
            to: picker.endDate
        });
    };

    DateRangePicker.prototype.initDateRangePicker = function () {
        var that = this;
        var dates = this.buildInitialDates();
        var ranges = this.buildRanges();

        this.$input.daterangepicker({
            autoUpdateInput: true,
            autoApply: true,
            alwaysShowCalendars: true,
            showWeekNumbers: true,
            startDate: dates.startDate,
            endDate: dates.endDate,
            locale: {
                format: 'DD.MM.YYYY'
            },
            ranges: ranges
        });

        // trigger update event when picker changes
        this.$input.on('apply.daterangepicker', $.proxy(this.triggerUpdate, this));

        // trigger update event initally
        this.triggerUpdate();
    };

    DateRangePicker.prototype.buildRanges = function () {
        var ranges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        };

        // add ranges defined by data attribute
        var dataRanges = this.$input.data('date-ranges');
        if (dataRanges && 'object' === typeof dataRanges) {
            $.each(dataRanges, function(label, range) {
                ranges[label] = [
                    moment(range.startDate, 'DD.MM.YYYY'),
                    moment(range.endDate, 'DD.MM.YYYY')
                ];
            });
        }

        return ranges;
    };

    DateRangePicker.prototype.buildInitialDates = function () {
        var startDate;
        if (this.$input.data('date-from')) {
            startDate = moment(this.$input.data('date-from'), 'DD.MM.YYYY');
        } else {
            startDate = moment().startOf('month');
        }

        var endDate;
        if (this.$input.data('date-to')) {
            endDate = moment(this.$input.data('date-to'), 'DD.MM.YYYY');
        } else {
            if (startDate) {
                endDate = startDate.clone().endOf('month');
            } else {
                endDate = moment().startOf('month');
            }
        }

        return {
            startDate: startDate,
            endDate: endDate
        };
    };

    return DateRangePicker;
}());
