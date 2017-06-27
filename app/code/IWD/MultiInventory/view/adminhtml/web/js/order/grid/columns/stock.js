define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'IWD_MultiInventory/js/order/warehouse/stock'
], function (Column, $, iwdWarehouse) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-stock-cell': true
            }
        },

        initialize: function () {
            iwdWarehouse.updateStockCell = this.updateStockCell;
            return this._super();
        },

        preview: function (row) {
            iwdWarehouse.preview(row);
        },

        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        },

        getLabel: function (row) {
            var data = this.prepareData(row);
            return iwdWarehouse.getLabel(data);
        },

        prepareData: function(row) {
            return {
                assignedQty : row['assignedQty'],
                orderedQty : row['orderedQty'],
                isOrderPlacesBefore : row['isOrderPlacesBefore'],
                refundedQty: row['refundedQty'],
                id : row['id'],
                isNotApplicable : row['isNotApplicable']
            };
        },

        updateStockCell: function(result) {
            var orderId = result['id'];
            $('.iwd-assign-stock-cell-' + orderId).replaceWith(iwdWarehouse.getLabel(result));
        }
    });
});
