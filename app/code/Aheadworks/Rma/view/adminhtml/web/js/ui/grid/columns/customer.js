/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Rma/ui/grid/cells/customer'
        },
        hasLink: function(row) {
            return row[this.index + '_url'];
        },
        getCustomerEmail: function(row) {
            return row[this.index + '_email'];
        },
        getCustomerName: function(row) {
            return row[this.index + '_name'];
        },
        getLinkUrl: function(row) {
            return row[this.index + '_url'];
        },
        getGuestCustomerName: function(row) {
            return row['customer_name'];
        },
        getGuestCustomerEmail: function(row) {
            return row['customer_email'];
        }
    });
});
