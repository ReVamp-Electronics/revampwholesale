/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            customerAttributesCheckout: 'Amasty_CustomerAttributes/js/view/checkout',
            customerAttributesCheckoutGuest: 'Amasty_CustomerAttributes/js/view/checkout-guest',
            customerAttributesAccount: 'Amasty_CustomerAttributes/js/view/register',
            "calendar":             "mage/calendar",
        }
    },
    config: {mixins: {
        'Magento_Checkout/js/action/set-shipping-information': {
            'Amasty_CustomerAttributes/js/action/set-shipping-information-mixin': true
        }
    }
}
};
