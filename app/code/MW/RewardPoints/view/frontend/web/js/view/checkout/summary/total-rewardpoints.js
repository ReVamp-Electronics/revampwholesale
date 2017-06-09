/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MW_RewardPoints/checkout/summary/total-rewardpoints'
            },
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {
                var totals = quote.getTotals()();
                if (totals.totals_rewardpoint) {
                    return totals.totals_rewardpoint;
                } else if (totals.totals_rewardpoint) {
                    return 0;
                } else {
                    return mwTotalRewardPoint;
                }
            },
            hasPoint: function() {
                if (this.getValue() != 0) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    }
);
