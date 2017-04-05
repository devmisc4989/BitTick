var bt_cdchart_config = {
    chartdata: false,
    chartObject: false,
    // After getting the response from the server, generates the chart animation with the corresponding data
    generateChart: function (chartdata) {
        this.chartdata = chartdata;
        this.bindDateChange();

        var columns = [['def', 0]];
        var types = {'def': 'line'};
        var x_axis = [];

        $('.chart_dates').find('.chart_title').html(chartdata.charttitle);

        if (chartdata.previousdate) {
            $('a.prev_date').css('visibility', 'visible');
        }
        if (chartdata.nextdate) {
            $('a.next_date').css('visibility', 'visible');
        }

        $.each(chartdata.x_labels.split(','), function (ind, level) {
            x_axis.push(level);
        });

        $.each(chartdata.conversions, function (index, conv) {
            var col1 = [];
            col1.push(chartdata.vnames[index]);
            $.each(conv.split(','), function (ind, tooltip) {
                col1.push(tooltip);
            });
            columns.push(col1);

            types[chartdata.vnames[index]] = 'line';
        });

        var col2 = [];
        col2.push(chartdata.imptitle);
        $.each(chartdata.impressions.split(','), function (index, imp) {
            col2.push(imp);
            columns.push(col2);
        });
        types[chartdata.imptitle] = 'bar';

        var colors = ['transparent'];
        $.each(chartdata.colors, function (index, c) {
            colors.push(c);
        });

        var data = {
            columns: columns,
            types: types,
            axes: {}
        };
        data.axes[chartdata.imptitle] = 'y2';

        this.chartObject = c3.generate({
            bindto: '#lpc_chart',
            data: data,
            size: {
                width: 955,
                height: 320
            },
            color: {
                pattern: colors
            },
            tooltip: {
                grouped: false,
                format: {
                    value: function (value, ratio, id) {
                        if (id !== chartdata.imptitle) {
                            var format = d3.format('%');
                            return chartdata.crtitle + ': ' + format(value / 100);
                        }
                        return value;
                    }
                }
            },
            grid: {
                y: {
                    show: true
                }
            },
            axis: {
                x: {
                    type: 'category',
                    categories: x_axis
                },
                y: {
                    max: chartdata.y_max,
                    padding: 2,
                    label: {
                        text: chartdata.y_legend,
                        position: 'outer-middle'
                    }
                },
                y2: {
                    show: true,
                    max: chartdata.maximp > 0 ? chartdata.maximp * 9.1 : 91,
                    label: {
                        text: chartdata.imptitle,
                        position: 'outer-middle'
                    }
                }
            },
            point: {
                r: 4
            }
        });

        $('.c3-line').css('stroke-width', '4px');
        $('.c3-ygrid').css('stroke-dasharray', '0');
        this.hideOrDisplayVariantLine();
    },
    // Depending on the "checked" variants checkboxes, we hide/display the corresponding variant line in the chart animation
    hideOrDisplayVariantLine: function () {
        var self = this;
        this.chartObject.show(null, {
            withLegend: true
        });

        $('#collectiondetails').find('tr').each(function () {
            if ($(this).find('.lpc_variant_name').length > 0) {
                var vname = $(this).find('.lpc_variant_name').data('vname');
                if (!$(this).find('input[type="checkbox"]').is(':checked')) {
                    self.chartObject.hide(vname, {
                        withLegend: true
                    });
                }
            }
        });
    },
    // calls the getCdChart controller to get the trend data statistics for the given project
    getCdChart: function (start) {
        $('#lpc_chart').empty();
        $('a.chart_date_links').css('visibility', 'hidden');
        var bgImg = 'url(' + BTeditorVars.BaseSslUrl + 'images/ajax-loader.gif)';
        $('#lpc_chart_container').css('background-image', bgImg);
        var self = this;

        $.ajax({
            type: "GET",
            url: BTeditorVars.BaseSslUrl + "lpc/getCdChart",
            dataType: 'json',
            data: {
                'lpcid': BTeditorVars.CollectionId || false,
                'groupid': BTeditorVars.GroupId || false,
                'goalid': $('#lpc_goal_select').length > 0 ? $('#lpc_goal_select').val() : $('.details_goal_name').attr('id'),
                'timeinterval': $('#lpc_interval_select').val(),
                'start': start
            }
        }).done(function (res) {
            self.generateChart(res);
            $('#lpc_chart_container').css('background-image', 'none');
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // verifies when clicking on a variant checkbox to display/hide the corresponding line in the chart animation
    bindCheckboxChange: function () {
        var self = this;
        $('.squarecheck').find('input[type="checkbox"]').on('click', function () {
            self.hideOrDisplayVariantLine();
        });
    },
    // When clicking on the date arrows (previous/next) we have to get the corresponding statistics for the selected date range
    bindDateChange: function () {
        var self = this;
        $('a.chart_date_links').off('click');

        $('a.chart_date_links').on('click', function () {
            var start = -1;

            if ($(this).hasClass('prev_date')) {
                start = self.chartdata.previousdate;
            } else {
                start = self.chartdata.nextdate;
            }

            self.getCdChart(start);
        });
    },
    init: function () {
        this.getCdChart(-1);
        this.bindCheckboxChange();
    }
};

$(document).on('ready', function () {
    bt_cdchart_config.init();
});