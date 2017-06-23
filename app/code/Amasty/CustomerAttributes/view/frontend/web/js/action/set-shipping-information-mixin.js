define([
    'jquery',
    'mage/utils/wrapper',
    'uiRegistry'
], function ($, wrapper, registry) {
    'use strict';

    return function (setShippingInformation) {
        return wrapper.wrap(setShippingInformation,
            function(originalAction) {
                 var source = registry.get('checkoutProvider')
                 source.set('params.invalid', false);
                 if (source.get('shippingAddress.custom_attributes')) {
                     source.trigger('shippingAddress.custom_attributes.data.validate');
                 };
                 if ( source.get('params.invalid') ) {
                     throw new Error('Form is invalid');
                     return false;
                 }
                return originalAction();
            }
        );
    };
});
