/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

define([
    'ko',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function (ko, _, registry, Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            return this;
        },
        onUpdate         : function () {
            this._super();
            if (this.relations && this.relations.length) {
                registry.get(this.parentName, function (fieldset) {
                    fieldset.dependsToShow = [];
                });
                this.checkDependencies();
            }
        },
        checkDependencies: function() {
            var fieldset = registry.get(this.parentName);
            if (this.relations && this.relations.length) {
                this.relations.map(function (relation) {
                    var dependElement = fieldset.getChild(relation.dependent_name);
                    if (dependElement) {
                        if (this.value() == relation.option_value && this.visible()) {
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
