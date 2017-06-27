define([
    'underscore',
    'uiRegistry',
    'mage/storage',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/form/element/select'
], function (_, registry, storage, loader, AbstractSelect) {
    'use strict';
    // make Ajax for new Attribute options
    function reloadOptions (url) {
        loader.show();
        storage.get(url).done(setOptions);
    }
    // set New Attribute Options
    function setOptions(response) {
        if (response.error) {
            alert(response.error);
        } else if(response.attribute_options != void(0) && response.dependent_attributes != void(0)) {
            registry.get('amcustomerattr_relation_form.amcustomerattr_relation_form.general.attribute_options', function (input) {
                input.setOptions(response.attribute_options);
                input.clear();
            });
            registry.get('amcustomerattr_relation_form.amcustomerattr_relation_form.general.dependent_attributes', function (input) {
                input.setOptions(response.dependent_attributes);
                input.clear();
            });
        }
        loader.hide();
    }

    return AbstractSelect.extend({
        onUpdate: function (data) {
            this._super();

            reloadOptions(this.update_url.replace(":aid", data));
        }
    });
});