define([
    'prototype',
    'jquery',
    'mage/calendar',
    'mage/translate'
], function() {
    jQuery.noConflict();

    if (typeof window.MW == 'undefined') {
        window.MW = {
            ProductView: {},
            RewardPoint: {
                SystemConfig: {
                    Caltax: {}
                },
                Report: {
                    Dashboard: {}
                }
            }
        };
    } else {
        window.MW.ProductView = {};
        window.MW.RewardPoint = {
            SystemConfig: {
                Caltax: {}
            },
            Report: {
                Dashboard: {}
            }
        }
    }

    window.MW.ProductView = Class.create();
    MW.ProductView.prototype = {
        index_price: 0,
        initialize: function() {
            var thisView = this;
            this._(false);

            try {
                $$('.mw_show_hide_set_point').invoke('observe', 'click', this.showHide.bind(this));

                $$("input[name*=reward_point_product], input[name*=mw_reward_point_sell_product]").each(function(el){
                    el.observe('keydown', thisView.pastePoint.bind(this));
                });

                $("mw-lb-set-point").observe('click', function(event){
                    var checkbox = $$('input[name=product\\[mw_show_hide_set_point\\]]');

                    $$('.mw_show_hide_set_point').each(function(element) {
                        if (element.checked) {
                            element.value = 0;
                            element.checked = false;
                        } else {
                            element.value = 1;
                            element.checked = true;
                        }
                        thisView.showHide(null, element);
                    });
                });
            } catch(e) {}
        },
        pastePoint: function(ev, element) {
            if (ev.keyCode == 67) {
                element = $(Event.element(ev));
                var index_price = 0;
                $$(".mw-reward-point-product thead th span").each(function(el, index){
                    if (el.innerText == 'Price') {
                        index_price = index;
                        return;
                    }
                });
                var price = element.up('tr').select('td:eq('+index_price+')')[0].innerText;
                if (price.indexOf('.')) {
                    price = price.split('.');
                    price = price[0].replace(/[^0-9\s]/gi, '');
                } else {
                    price = price[0].replace(/[^0-9\s]/gi, '');
                }
                element.setValue(price);
                Event.stop(ev);
            }
        },
        _: function(flag) {
            /** Show/Hide label Sell Products in Points */
            $$('label[for=mw_reward_point_sell_product]').each(function(s) {
                if (flag) {
                    $(s).up('tr').show();
                } else {
                    $(s).up('tr').hide();
                }
            });

            /** Change text label "Reward Points Earned" to "Set Points to Earn/Reward" or otherwise  */
            $$('label[for=reward_point_product]').each(function(s) {
                if (flag) {
                    $(s).update("Reward Points Earned");
                } else{
                    $(s).update("Set Points to Earn/Sell in Point");
                }
            });
        },
        showHide: function(ev, element) {
            if (element == undefined) {
                element = $(Event.element(ev));
            }

            this._(element.checked);

            if (element.checked) {
                $$(".mw-reward-point-product").each(Element.hide);
                $$(".mw-reward-point-input").each(Element.show);
                element.value = 1;
            } else {
                $$(".mw-reward-point-product").each(Element.show);
                $$(".mw-reward-point-input").each(Element.hide);
                element.value = 0;
            }
        }
    };

    window.MW.RewardPoint.SystemConfig.Caltax = Class.create();
    MW.RewardPoint.SystemConfig.Caltax.prototype =
    {
        initialize: function(params) {
            this.params = params;
            try {
                this._($(params.element).value);
                $(params.element).observe('change', this.onChange.bind(this));
            } catch(e) {
                console.log(e);
            }
        },
        onChange: function(ev, element) {
            if (element == undefined) {
                element = $(Event.element(ev));
            }

            this._(element.value);
        },
        _: function(value) {
            if (value == this.params.AFTER_VALUE) {
                $("row_rewardpoints_general_redeemed_tax").hide();
                $("row_rewardpoints_general_redeemed_shipping").hide();
            } else if (value == this.params.BEFORE_VALUE) {
                $("row_rewardpoints_general_redeemed_tax").show();
                $("row_rewardpoints_general_redeemed_shipping").show();
            }
        }
    };

    window.MW.RewardPoint.Report.Dashboard = Class.create();
    MW.RewardPoint.Report.Dashboard.prototype = {
        initialize: function(params) {
            var self = this;
            this.params = params;

            Event.observe('report_range', 'change', this.onChangeRange.bind(this));
            Event.observe('report_refresh', 'click', this.onClickRefresh.bind(this));
            Event.observe(window, 'keypress', this.onWindowKeypress.bind(this));
            this.onChangeRange(null, $("report_range"));

            jQuery("#report_from").calendar({
                showsTime: true,
                dateFormat: 'mm/dd/yy',
                timeFormat: 'HH:mm:ss TT'
            });
            jQuery("#report_to").calendar({
                showsTime: true,
                dateFormat: 'mm/dd/yy',
                timeFormat: 'HH:mm:ss TT'
            });
        },
        onWindowKeypress: function(event, element) {
            console.log(event.keyCode);
            if (Event.KEY_RETURN == event.keyCode) {
                this.onChangeRange(null, $("report_range"));
            }

            if (event.keyCode == 115) {
                var output = '';
                for (property in this.statistics) {
                    output += property + ': ' + this.statistics[property]+"; <br>\n";
                }
                $("debug").innerHTML = output;
            }
            if (event.keyCode == 99) {
                $("debug").innerHTML = '';
            }
        },
        onClickRefresh: function(event, element) {
            var self = this;
            new Ajax.Request(this.params.url, {
                method: 'post',
                parameters: {
                    ajax: true,
                    report_range: $("report_range").value,
                    from: $("report_from").value,
                    to: $("report_to").value,
                    type: 'dashboard'
                },
                onSuccess: function(transport) {
                    if (transport.responseText) {
                        var data = transport.responseText.evalJSON();
                        self.data = data.report;
                        self.buildChart(parseInt($("report_range").value));
                        self.buildPieChart(data.report_activities);
                        self.fillDataStats(data.statistics);
                    } else {
                        /** Draw empty grah */
                    }
                }
            });
        },
        onChangeRange: function(event, element) {
            var self = this;

            if (element == undefined) {
                element = $(Event.element(event));
            }
            if (parseInt(element.value) == 7) {
                $("custom_range").show();

                return false;
            }

            $("custom_range").hide();
            new Ajax.Request(this.params.url, {
                method: 'post',
                parameters: {
                    ajax: true,
                    report_range: element.value,
                    type: 'dashboard'
                },
                onSuccess: function(transport) {
                    if (transport.responseText) {
                        var data = transport.responseText.evalJSON();
                        self.data = data.report;
                        self.buildChart(parseInt(element.value));
                        self.buildPieChart(data.report_activities);
                        self.fillDataStats(data.statistics);
                        self.statistics = data.statistics;
                    } else {
                        /** Draw empty grah */
                    }
                }
            });
        },
        runningPieChart: function() {
            var self = this;

            new Ajax.Request(this.params.url, {
                method: 'post',
                parameters: {
                    ajax: true,
                    type: 'circle'
                },
                onSuccess: function(transport){
                    if (transport.responseText) {
                        self.buildPieChart(transport.responseText);
                    } else {
                        /** Draw empty grah */
                    }
                }
            });
        },
        fillDataStats: function(data) {
            $("total_rewarded").innerHTML = data.total_rewarded_sum;
            $("total_redeemed").innerHTML = data.total_redeemed_sum;
            $("total_customer_bal").innerHTML = data.total_point_customer;
            $("avg_rewarded_customer").innerHTML = data.avg_reward_per_customer;
            $("avg_rewarded_order").innerHTML = data.avg_rewarded_per_order;
            $("avg_redeemded_order").innerHTML = data.avg_redeemed_per_order;
        },
        buildPieChart: function(data) {
            var data = data.evalJSON();
            var _data = new Array();
            if (Object.keys(data).length == 0) {
                $("rwp-container-pie").innerHTML = '<span style="color: #ccc; text-align: center; display: block;">'+ jQuery.mage.__('No Record')+'</span>';
                return false;
            }
            for (var i = 0; i < Object.keys(data).length; i++) {
                var _data_item = new Array();
                _data_item.push(data[i][0], data[i][1]);
                _data.push(_data_item);
            }

            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'rwp-container-pie',
                    plotBackgroundColor: null,
                    plotBorderWidth: 1,
                    plotShadow: false
                },
                exporting:{
                    enabled: false
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.2f}%</b>'
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    x: 0,
                    verticalAlign: 'top',
                    y: 20,
                    floating: true,
                    backgroundColor: 'transparent',
                    labelFormatter: function() {
                        return this.name+ ": "+this.y + "%";
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            connectorWidth: 2,
                            format: '{point.name}: {point.y:.2f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Browser share',
                    data: _data
                }]
            });
        },
        buildChart: function(type) {
            var self = this;
            self.buildOptionChart(type);

            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'rwp-container',
                },
                exporting:{
                    enabled: false
                },
                title: {
                    text: self.data.title
                },
                subtitle: {
                    text: ''
                },
                xAxis: self.xAxis,
                yAxis: [{ // Secondary yAxis
                    title: {
                        text: 'Rewarded Points',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                    labels: {
                        format: '{value:.,0f}',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                },
                    { // Primary yAxis
                        labels: {
                            format: '{value:.,0f}',
                            style: {
                                color: Highcharts.getOptions().colors[1]
                            }
                        },
                        title: {
                            text: 'Redeemed Points',
                            style: {
                                color: Highcharts.getOptions().colors[1]
                            }
                        },
                        opposite: true
                    }],
                tooltip: {
                    shared: true,
                    crosshairs: true
                },
                plotOptions: {
                    series: {
                        fillOpacity: 0.1
                    },
                    area: {
                        pointStart: 1940,
                        marker: {
                            enabled: true,
                            symbol: 'circle',
                            radius: 2,
                            states: {
                                hover: {
                                    enabled: true
                                }
                            }
                        }
                    }
                },
                series: this.series
            });
        },
        buildOptionChart: function(type) {
            var self = this;
            var data_redeemed = null;
            var data_rewarded = null;
            switch(type) {
                case 1: // Last 24 hours
                    self.xAxis = {
                        type: 'datetime',
                        labels: {
                            format: '{value:%H:%M}',
                            //rotation: 45,
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d, self.data.date_start.h);
                    var pointInterval = 1 * 3600 * 1000;

                    data_redeemed = self.data.redeemed;
                    data_rewarded = self.data.rewarded;
                    break;
                case 2: // Last week
                    self.xAxis = {
                        type: 'datetime',
                        tickInterval: 24 * 3600 * 1000,
                        labels: {
                            format: '{value:%b %d}',
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d);
                    var pointInterval = 24 * 3600 * 1000;

                    data_redeemed = self.data.redeemed;
                    data_rewarded = self.data.rewarded;
                    break;
                case 3: // Last month
                    self.xAxis = {
                        type: 'datetime',
                        tickInterval: 7 * 24 * 3600 * 1000,
                        labels: {
                            format: '{value:%b %d}',
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d);
                    var pointInterval = 24 * 3600 * 1000;

                    data_redeemed = self.data.redeemed;
                    data_rewarded = self.data.rewarded;
                    break;
                case 4: // Last 7 days
                case 5: // Last 30 days
                case 7: // Custom range
                    self.xAxis = {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            month: '%e. %b',
                            year: '%b'
                        },
                    };

                    var redeemed = new Array();
                    self.data.redeemed.each(function(value, k){
                        redeemed.push([Date.UTC(value[0],  value[1] - 1,  value[2]), value[3]]);
                    });

                    var rewarded = new Array();
                    self.data.rewarded.each(function(value, k){
                        rewarded.push([Date.UTC(value[0],  value[1] - 1,  value[2]), value[3]]);
                    });

                    data_redeemed = redeemed;
                    data_rewarded = rewarded;

                    var pointStart = null;
                    var pointInterval = 24 * 3600 * 1000;
                    break;
            }

            this.series = [{
                name: 'Rewarded Points',
                type: 'area',
                color: '#C74204',
                data: data_rewarded,
                tooltip: {
                    valueSuffix: ' point(s)'
                },
                pointStart: pointStart,
                pointInterval: pointInterval,
            },
            {
                name: 'Redeemed Points',
                type: 'area',
                color: '#0481C7',
                data: data_redeemed,
                yAxis: 1,
                tooltip: {
                    valueSuffix: ' point(s)'
                },
                pointStart: pointStart,
                pointInterval: pointInterval,

            }];
        },
        print_r: function(printthis, returnoutput) {
            var output = '';
            var self = this;
            for (var i in printthis) {
                output += i + ' : ' + self.print_r(printthis[i], true) + '\n';
            }
            if (returnoutput && returnoutput == true) {
                return output;
            } else {
                alert(output);
            }
        }
    }
});
