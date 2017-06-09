define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'IWD_MultiInventory/js/product/inventory/stock-data'
], function ($, _, Component, ko, stockData) {
    'use strict';

    return Component.extend({
        defaultStockId: 1,
        defaults: {
            template: 'IWD_MultiInventory/product/inventory/modal'
        },

        isProductComposite: function() {
            return stockData.isProductComposite();
        },

        isComplexProduct: function() {
            return stockData.isComplexProduct();
        },

        canUseQtyDecimals: function() {
            return stockData.canUseQtyDecimals();
        },

        isVirtual: function() {
            return stockData.isVirtual();
        },

        isReadonly: function() {
            return stockData.isReadonly();
        },

        getFieldName: function(id, field) {
            if (id == 1) {
                return "product[stock_data][" + field + "]";
            }
            return "product[iwd_stock_data][" + id + "][" + field + "]";
        },

        getFieldId: function(id, field) {
            return field + "_" + id ;
        },

        getTableRowClass: function(id) {
            return  "tr_" + id + (this.isDefaultStock(id) ? " total-row" : "");
        },

        isDefaultStock: function(id) {
            return (this.defaultStockId == id);
        },

        getStocks: function() {
            var stocks = stockData.getStocks();
            var isNew = stockData.getIsProductNew();

            $.each(stocks, function(i, stock){
                stock.useConfigManageStock = ko.observable(isNew || stock.use_config_manage_stock == 1);
                stock.manageStock = ko.observable(stock.manage_stock == 1);
                stock.manageStockDisabledClass = ko.computed(function() {return stock.useConfigManageStock() ? 'disabled' : '';}, stock);
                stock.stockQty =  ko.observable(stock.qty * 1);
            });

            return stocks;
        },

        getDefaultStocks: function() {
            var stocks = stockData.getDefaultStock();
            var isNew = stockData.getIsProductNew();

            $.each(stocks, function(i, stock){
                stock.manageStock = ko.observable(stock.manage_stock == 1);
                stock.configManageStock = ko.observable(isNew || stock.use_config_manage_stock == 1);
                stock.useConfigManageStock =  ko.pureComputed({
                    read: stock.configManageStock,
                    write: function (value) {
                        var useConfig = value ? stock.config_manage_stock == 1 : stock.manage_stock == 1;
                        this.manageStock(useConfig);
                        stock.configManageStock(value);
                    },
                    owner: stock
                });
                stock.manageStockDisabledClass = ko.computed(function() {return stock.configManageStock() ? 'disabled' : '';}, stock);

                stock.stockQty =  ko.observable(stock.qty * 1);

                stock.useConfigBackorders = ko.observable(isNew || stock.use_config_backorders == 1);
                stock.valueBackorders = ko.computed(function() {return stock.useConfigBackorders() ? stock.config_backorders * 1 : stock.backorders * 1;}, stock);

                stock.useConfigInventoryMinQty = ko.observable(isNew || stock.use_config_min_qty == 1);
                stock.valueMinQty = ko.computed(function() {return stock.useConfigInventoryMinQty() ? stock.config_min_qty * 1 : stock.min_qty * 1;}, stock);

                stock.useConfigInventoryMinSaleQty = ko.observable(isNew || stock.use_config_min_sale_qty == 1);
                stock.valueInventoryMinSaleQty = ko.computed(function() {return stock.useConfigInventoryMinSaleQty() ? stock.config_min_sale_qty * 1 : stock.min_sale_qty * 1;}, stock);

                stock.useConfigInventoryMaxSaleQty = ko.observable(isNew || stock.use_config_max_sale_qty == 1);
                stock.valueInventoryMaxSaleQty = ko.computed(function() {return stock.useConfigInventoryMaxSaleQty() ? stock.config_max_sale_qty * 1 : stock.max_sale_qty * 1;}, stock);

                stock.useConfigNotifyStockQty = ko.observable(isNew || stock.use_config_notify_stock_qty == 1);
                stock.valueNotifyStockQty = ko.computed(function() {return stock.useConfigNotifyStockQty() ? stock.config_notify_stock_qty * 1 : stock.notify_stock_qty * 1;}, stock);

                stock.enableQtyIncrements = ko.observable(stock.enable_qty_increments == 1);
                stock.configEnableQtyInc = ko.observable(isNew || stock.use_config_enable_qty_inc == 1);
                stock.useConfigEnableQtyInc = ko.pureComputed({
                    read: stock.configEnableQtyInc,
                    write: function (value) {
                        var useConfig = value ? stock.config_enable_qty_inc == 1 : stock.enable_qty_inc == 1;
                        this.enableQtyIncrements(useConfig);
                        this.configEnableQtyInc(value);
                    },
                    owner: stock
                });
                stock.enableQtyIncDisabledClass = ko.computed(function() {return stock.configEnableQtyInc() ? 'disabled' : '';}, stock);

                stock.useConfigQtyIncrements = ko.observable(isNew || stock.use_config_qty_increments == 1);
                stock.valueQtyIncrements = ko.computed(function() {return stock.useConfigQtyIncrements() ? stock.config_qty_increments * 1 : stock.qty_increments * 1;}, stock);
            });

            return stocks;
        },

        getBackordersOption: function() {
            return stockData.getBackordersOption();
        },

        getStockOption: function() {
            return stockData.getStockOption();
        }
    });
});
