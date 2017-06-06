/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (SingleCheckbox, alert, $t) {
    'use strict';

    return SingleCheckbox.extend({

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.isDefaultDepartment = (this.source.get('data.is_default') == 1);
            return this;
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            if (this.isDefaultDepartment && !newChecked) {
                this.checked(true);
                alert({
                    title: $t('Default department'),
                    content: $t('Default department can not be disabled'),
                    actions: {
                        always: function(){}
                    }
                });
            } else {
                this._super(newChecked);
            }
        },
    });
});
