define([
        'ko',
        'jquery',
        'uiComponent'
    ],
    function(ko, $, Component) {
        return Component.extend({

            defaults: {
                template: 'IWD_OrderManager/order/grid/totals/totals',
                listens: {
                    'sales_order_grid.sales_order_grid_data_source:reloaded': 'onDataReloaded'
                },
                imports: {
                    iwdTotals: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals',
                    defaultTotals: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals.defaultTotals',
                    additionalTotals: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals.additionalTotals',
                    pageFrom: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals.options.pageFrom',
                    pageTo: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals.options.pageTo',
                    ordersCount: 'sales_order_grid.sales_order_grid_data_source:data.iwdTotals.options.ordersCount'
                }
            },

            onDataReloaded: function() {
                var self = this;
                var lazyLayout = _.debounce(function () {
                    self.defaultTableResponsive();
                    self.moreTableResponsive();
                }, 1000);

                lazyLayout();
            },

            initObservable: function () {
                this._super()
                    .track(['iwdTotals', 'defaultTotals', 'additionalTotals', 'pageFrom', 'pageTo', 'ordersCount']);
                return this;
            },

            initialize: function () {
                this.tablesResponsive();
                return this._super();
            },

            tablesResponsive: function () {
                var self = this;
                var lazyLayout = _.debounce(function () {
                    self.defaultTableResponsive();
                    self.moreTableResponsive();
                }, 500);
                $(window).resize(lazyLayout);
            },

            defaultTableResponsive: function() {
                var defaultTable = '.iwd-om-totals .default-totals';
                $(defaultTable).removeClass('big-data');
                $(defaultTable + ' .total-block').each(function(){
                    var titleWidth = $(this).find('.total-title').outerWidth() + 15;
                    var amountWidth = $(this).find('.total-amount').outerWidth() + 15;
                    if ($(this).innerWidth() < (titleWidth + amountWidth)) {
                        $(defaultTable).addClass('big-data');
                        return 0;
                    }
                });
            },

            moreTableResponsive: function() {
                var moreTable = '.iwd-om-totals .more-totals';
                $(moreTable).removeClass('big-data');
                $(moreTable + ' .total-block').each(function(){
                    var titleWidth = $(this).find('.total-title').outerWidth() + 15;
                    var amountWidth = $(this).find('.total-amount').outerWidth() + 15;
                    if ($(this).innerWidth() < titleWidth || $(this).innerWidth() < amountWidth) {
                        $(moreTable).addClass('big-data');
                        return 0;
                    }
                });
            },

            showMoreLess: ko.observable(false),

            showMoreLessClick: function() {
                this.showMoreLess(!this.showMoreLess());
                this.defaultTableResponsive();
                this.moreTableResponsive();
            },

            orderLabel:function(item) {
                return item.label + ' total';
            },
            pageLabel:function(item) {
                return item.label + ' page total';
            },
            ordersCountLabel:function() {
                if (this.ordersCount == 1) {
                    return '<b>' + this.ordersCount + '</b> order';
                }
                return '<b>' + this.ordersCount + '</b> orders';
            },
            fromToPageLabel:function() {
                if (this.pageFrom == 1 && this.pageTo == 1) {
                    return '<b>' + this.pageTo + '</b> order';
                }
                if (this.pageTo == 0) {
                    return '<b>' + this.pageTo + '</b> orders';
                }

                return '<b>' + this.pageFrom + ' to ' + this.pageTo + '</b> orders';
            }
        });
    });