/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/customer-data'
    ],
    function($, ko, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amasty_CustomerAttributes/register'
            },
            visible: function(){
                return true;
            },
            initialize: function() {
                var self = this;
                this._super();
                var a = customerData.reload();
                var customer = customerData.get('customer');
            },
            setPosition: function(element){
                $(element).appendTo("#form-validate");
                var loaderContainer = document.getElementById('checkout-loader');

                if (loaderContainer && loaderContainer.parentNode) {
                    loaderContainer.parentNode.removeChild(loaderContainer);
                }
            }
        });
    }
);
