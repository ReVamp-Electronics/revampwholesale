
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/multiselect'
], function (_, registry, Multiselect) {
    'use strict';

    return Multiselect.extend({
        onUpdate         : function () {
            this._super();
            registry.get(this.parentName, function (fieldset) {
                fieldset.dependsToShow = [];
            });
            this.checkDependencies();
        },

        checkDependencies: function() {
            var fieldset = registry.get(this.parentName);
            if (this.relations && this.relations.length) {
                this.relations.map(function (relation) {
                    var dependElement = fieldset.getChild(relation.dependent_name);
                    if (dependElement) {
                        if (this.value().indexOf(relation.option_value) >= 0 && this.visible()) {
                            this.showDepend(dependElement);
                        } else if (fieldset.dependsToShow.indexOf(relation.dependent_name) < 0) {
                            /** hide element only if no relation rules to show. On one check */
                            this.hideDepend(dependElement);
                        }
                    }
                }.bind(this));
            }
        },

        showDepend: function (dependElement) {
            dependElement.show();
            registry.get(this.parentName).dependsToShow.push(dependElement.index);
            if (_.isFunction(dependElement.checkDependencies)) {
                dependElement.checkDependencies();
            }
        },

        hideDepend: function (dependElement) {
            dependElement.hide();
            if (_.isFunction(dependElement.checkDependencies)) {
                dependElement.checkDependencies();
            }
        }
    });
});
