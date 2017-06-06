/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (SingleCheckbox, confirm, alert, $t) {
    'use strict';

    return SingleCheckbox.extend({

        /**
         * Used to hide confirm pop-up when it is not needed
         */
        displayConfirm: false,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.isDefaultDepartment = (this.source.get('data.is_default') == 1);
            this.displayConfirm = true;
            return this;
        },

        /**
         * @inheritdoc
         */
         onCheckedChanged: function (newChecked) {
             if (this.isDefaultDepartment && !newChecked) {
                 this.displayConfirm = false;
                 this.checked(true);
                 alert({
                     title: $t('Default department'),
                     content: $t('Default department can not be changed. Please select a new one first.'),
                     actions: {
                         always: function(){}
                     }
                 });
             } else {
                 if (newChecked && this.displayConfirm) {
                     confirm({
                         title: $t('Default department'),
                         content: $t('Only one department can be a default one (per website). Are you sure you want to do this?'),
                         actions: {
                             confirm: function(){
                                 this.displayConfirm = false;
                                 this.onCheckedChanged(newChecked);
                             }.bind(this),
                             cancel: function(){
                                 this.onCheckedChanged(false);
                             }.bind(this),
                             always: function(){}
                         }
                     });
                 } else {
                     this._super(newChecked);
                     this.displayConfirm = true;
                 }
             }
        },
    });
});
