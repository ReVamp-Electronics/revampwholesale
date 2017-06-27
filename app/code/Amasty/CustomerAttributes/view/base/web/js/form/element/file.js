/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/media'
], function (_, registry, Media) {
    'use strict';

    return Media.extend({
        defaults: {
            elementTmpl: 'Amasty_CustomerAttributes/form/element/media',
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        initialize: function () {
            this._super();
            if (this.value()) {
                this.value(this.path + this.value());
            }

            return this;
        }

    });
});
