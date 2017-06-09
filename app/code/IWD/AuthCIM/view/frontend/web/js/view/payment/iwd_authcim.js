/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        var component = window.checkoutConfig.payment.iwd_authcim.isAcceptjsEnabled
            ? 'IWD_AuthCIM/js/view/payment/method-renderer/acceptjs'
            : 'IWD_AuthCIM/js/view/payment/method-renderer/iframe';

        rendererList.push(
            {
                type: 'iwd_authcim',
                component: component
            }
        );

        /**
         * Add view logic here if needed
         */
        return Component.extend({

        });
    }
);