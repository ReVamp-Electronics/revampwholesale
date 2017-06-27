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
            bodyTmpl: 'Aheadworks_Rma/ui/grid/cells/link'
        },
        hasLink: function(row) {
            return row[this.index + '_url'];
        },
        getPlainText: function(row) {
            return row[this.index];
        },
        getLinkText: function(row) {
            return row[this.index + '_text'];
        },
        getLinkHint: function(row) {
            return row[this.index + '_hint'];
        },
        getLinkUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});
