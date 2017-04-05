// LPC Scheduler
$(window).bind('ready', function () {
    var lpc_scheduler = {
        m: [],
        ms: [],
        d: [],
        dm: [],
        ds: [],
        // Fills the arrays of day and month names according to the language, then calls the datepicker init method
        fillDayMonthArray: function () {
            var self = this;
            $.each($('#datepicker_locale_month').val().split(','), function (ind, month) {
                self.m.push(month);
            });
            $.each($('#datepicker_locale_month_short').val().split(','), function (ind, short) {
                self.ms.push(short);
            });
            $.each($('#datepicker_locale_days').val().split(','), function (ind, day) {
                self.d.push(day);
            });
            $.each($('#datepicker_locale_days_min').val().split(','), function (ind, min) {
                self.dm.push(min);
            });
            $.each($('#datepicker_locale_days_short').val().split(','), function (ind, short) {
                self.ds.push(short);
            });

            this.initDatePicker('start');
            this.initDatePicker('end');
        },
        // initializes the datepicker plugin for the given element (start / end)
        initDatePicker: function (elem) {
            var self = this;
            $('#lpc_' + elem + '_datepicker').DatePicker({
                flat: true,
                date: $('#lpc_' + elem + '_date').val(),
                current: $('#lpc_' + elem + '_date').val(),
                calendars: 1,
                starts: 1,
                mode: 'single',
                next: '',
                prev: '',
                format: 'd.m.Y',
                locale: {
                    "months": self.m,
                    "monthsShort": self.ms,
                    "days": self.d,
                    "daysMin": self.dm,
                    "daysShort": self.ds,
                    "weekMin": "W"
                },
                onChange: function (formated, dates) {
                    var cur = $('#lpc_' + elem + '_date').val().replace(/ /g, '');
                    if (formated !== 'NaN.NaN.NaN' && formated !== cur) {
                        $('#lpc_' + elem + '_date').val(formated);
                        $('.lpc_timeframe').fadeOut(0);
                    }
                },
                onRender: function (date) {
                    var today = new Date();
                    var yesterday = today.setDate(today.getDate() - 1); 
                    return {
                        disabled: (date.valueOf() <= yesterday.valueOf()),
                        className: date.valueOf() === new Date().getTime().valueOf() ? 'today' : false
                    };
                }
            });
        },
        // When clicking on a "calendar" icon the datepicker container is displayed/hidden according to the current state
        bindCalendarClick: function () {
            $('html').on('click', function () {
                $('.lpc_timeframe').fadeOut(0);
            });

            $('.lpc_timeframe').on('click', function (event) {
                event.stopPropagation();
            });

            $('a.calendar_icon').on('click', function (event) {
                event.stopPropagation();
                if ($(this).hasClass('disabled'))
                    return false;

                var $hideElem = ($(this).attr('id') === 'lpc_start_calendar') ? $('#lpc_end_timeframe') : $('#lpc_start_timeframe');
                $hideElem.fadeOut(0);

                var iconId = $(this).attr('id');
                $('.' + iconId + '.lpc_timeframe').fadeToggle(0);
            });
        },
        // checks the current start and end time to preselect it in the corresponding selects
        preselectCurrentTime: function () {
            var curStart = $('#lpc_current_start_time').val().split(':');
            $('#lpc_start_time').val(curStart[0] + ':00:00');
            $('#lpc_end_time').val($('#lpc_current_end_time').val());
        },
        // date inputs can't be disabled 'cause the value won't be sent on submit, so we disable this events
        disableDateInput: function () {
            $('.start_end_text').on('click focus', function (event) {
                event.stopPropagation();
                $(this).blur();
                return false;
            });
        },
        init: function () {
            this.fillDayMonthArray();
            this.bindCalendarClick();
            this.preselectCurrentTime();
            this.disableDateInput();
        }
    };
    lpc_scheduler.init();
});