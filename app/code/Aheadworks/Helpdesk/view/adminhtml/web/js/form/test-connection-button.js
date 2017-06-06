/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/components/button',
    'Magento_Ui/js/modal/alert',
    'jquery',
    'Magento_Ui/js/lib/spinner',
    'mage/translate',
], function (Button, alert, $, spinner, $t) {
    'use strict';

    return Button.extend({
        /**
         * @inheritdoc
         */
        applyAction: function (action) {
            var gatewayData = {
                protocol: this.source.get('data.gateway.protocol'),
                host: this.source.get('data.gateway.host'),
                login: this.source.get('data.gateway.login'),
                password: this.source.get('data.gateway.password'),
                secure_type: this.source.get('data.gateway.secure_type'),
                port: this.source.get('data.gateway.port')
            };

            spinner.show();

            $.ajax({
                url: action.url,
                type: "POST",
                dataType: 'json',
                data: {
                    gateway_data: gatewayData
                },
                complete: function(response) {
                    spinner.hide();
                    try {
                        response = $.parseJSON(response.responseText);
                        var resultMessage = response.message;

                        if (response.valid == 1) {
                            alert({
                                title: $t('Test connection'),
                                content: '<span class="success">' +
                                            resultMessage +
                                        '</span>',
                                actions: {
                                    always: function(){}
                                }
                            });
                        } else {
                            var errorMessage = $t('Error: %1');
                            alert({
                                title: $t('Test connection'),
                                content: '<span class="error">' +
                                        errorMessage.replace('%1', resultMessage) +
                                        '</span>',
                                actions: {
                                    always: function(){}
                                }
                            });
                        }
                    } catch (e) {
                        alert({
                            title: $t('Test connection'),
                            content: '<span class="error">' +
                                    $t('Oops, something went wrong. Please check gateway settings and try again.') +
                                    '</span>',
                            actions: {
                                always: function(){}
                            }
                        });
                    }
                }
            });
        },

        /**
         * Hide element
         *
         * @returns {Abstract} Chainable
         */
        hide: function () {
            this.visible(false);

            return this;
        },

        /**
         * Show element
         *
         * @returns {Abstract} Chainable
         */
        show: function () {
            this.visible(true);

            return this;
        },
    });
});
