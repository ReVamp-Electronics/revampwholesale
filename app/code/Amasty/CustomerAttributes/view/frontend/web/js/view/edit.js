/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/customer-data',
        'uiRegistry',
    ],
    function($, ko, Component, customerData, registry) {
        'use strict';
        var cacheKey = 'customer-attributes';

        var getData = function () {
            return storage.get(cacheKey)();
        };

        var saveData = function (checkoutData) {
            storage.set(cacheKey, checkoutData);
        };

        return Component.extend({
            defaults: {
                template: 'Amasty_CustomerAttributes/edit'
            },
            initialize: function() {
                var self = this;
                this._super();
                customerData.setCustomerAttributes = function (data) {
                    var obj = getData();
                    obj.customerAttributes = data;
                    saveData(obj);
                };
                customerData.getCustomerAttributes = function () {
                    return getData().customerAttributes;
                },
                registry.async('checkoutProvider')(function (provider) {
                    var customerAttributes = customerData.getCustomerAttributes();
                    if (customerAttributes) {
                        provider.set(
                            'customerAttributes',
                            $.extend({}, provider.get('customerAttributes'), shippingAddressData)
                        );
                    }
                    provider.on('customerAttributes', function (customerAttributes) {
                        customerData.setCustomerAttributes(customerAttributes);
                    });
                });
            },
            setPosition: function(element){
                $(element).insertAfter("fieldset.fieldset.password");
                var loaderContainer = document.getElementById('checkout-loader');

                if (loaderContainer && loaderContainer.parentNode) {
                    loaderContainer.parentNode.removeChild(loaderContainer);
                }
            }
        });
    }
);