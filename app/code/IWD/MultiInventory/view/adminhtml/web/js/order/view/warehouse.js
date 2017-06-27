define([
        'jquery',
        'IWD_MultiInventory/js/order/warehouse/stock',
        'jquery/ui'
    ],

    function ($, iwdMultiInventoryWarehouse) {
        'use strict';

        $.widget('mage.iwdMultiInventoryOrderViewWarehouse', {
            options: {
                stockOrderItems: {},
                orderId: 0
            },

            init: function (options) {
                this.options = this._margeOptions(this.options, options);
                iwdMultiInventoryWarehouse.init(this.options);
                iwdMultiInventoryWarehouse.updateStockCell = function(result){
                    var stockOrderItems = result['stockOrderItems'];
                    $.each(stockOrderItems, function (i, row) {
                        $('#assign_stock_' + i).html(iwdMultiInventoryWarehouse.getLabel(row));
                    });
                };

                this.initWarehouseColumnValues();
            },

            _margeOptions: function (options1, options2) {
                $.each(options2, function (i, e) {
                    options1[i] = e;
                });
                return options1;
            },

            initWarehouseColumnValues: function () {
                var self = this;
                $.each(this.options.stockOrderItems, function (i, row) {
                    $('#assign_stock_' + i).html(iwdMultiInventoryWarehouse.getLabel(row));
                });

                $('.col-assign-stock').on('click touchstart', function () {
                    iwdMultiInventoryWarehouse.preview({'id': self.options.orderId, 'isNotApplicable': self.options.isNotApplicable});
                });
            }
        });

        return $.mage.iwdMultiInventoryOrderViewWarehouse;
    });