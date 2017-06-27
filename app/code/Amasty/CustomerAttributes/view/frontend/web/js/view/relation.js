/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery'
    ],
    function($) {
        'use strict';
        return {
            config : {
                /**
                 * arrays with keys:
                 *      'parent_attribute_id'
                 *      'parent_attribute_code'
                 *      'parent_option_id'
                 *      'depend_attribute_id'
                 *      'depend_attribute_code'
                 *      'parent_attribute_element_uid'
                 *      'depend_attribute_element_uid'
                 */
            },
            indexedElements : [],
            init: function (options) {
                this.config = options.depends;
                this.initElements();
                return this;
            },
            // init parent element listeners
            initElements: function() {
                var different = [];
                $.each(this.config, function(key, relation) {
                    var element = this.getElement(relation.parent_attribute_element_uid);
                    if (element != void(0)) {
                        if ($.inArray(element.selector, different) == -1) {
                            different.push(element.selector);
                            element.on('change', function (event) {
                                this.observer(event);
                                this.indexedElements = [];
                            }.bind(this));
                            // for custom check
                            element.on('check_relations', this.observer.bind(this));
                            element.find('input,select').each(function (key, input) {
                                $(input).trigger("check_relations");
                            });
                            this.indexedElements = [];
                        }
                    }
                }.bind(this));
            },
            getElement: function (id) {
                return $('[data-ui-id="' + id + '"]');
            },
            observer: function (event) {
                var element = $(event.target);
                var block = $(event.currentTarget);
                if (element && block) {
                    var elementId = block.attr('data-ui-id');
                    this.runDependencies(element, elementId);
                }
            },
            runDependencies: function (element, elementId) {
                // Find dependents elements
                var dep = this.findElementRelations(elementId);
                // Iterate throw elements and show required elements
                $.each(dep, function(key, el) {
                    if (this.getElement(el.depend_id).length) {
                            // Multiselect and select
                        if (
                            (element.is(':checked') === true || !element.is('input'))
                            && (element.val() == el.value || element.val().indexOf(el.value) != -1)
                            && element.is(":visible")
                        ) {
                            this.showBlock(el.depend_id);
                        } else if (this.indexedElements.indexOf(el.depend_id) < 0) {
                            this.hideBlock(el.depend_id);
                        }

                    }
                }.bind(this));
            },
            hideBlock: function (id) {
                var element = this.getElement(id);
                element.hide();
                element.find('input,select').each(function (key, input) {
                    $(input).trigger("check_relations");
                });
            },
            showBlock: function (id) {
                var element = this.getElement(id);
                element.show();
                this.indexedElements.push(id);
                element.find('input,select').each(function (key, input) {
                    $(input).trigger("check_relations");
                });
            },
            findElementRelations: function (elementUId) {
                var elements = [];
                $.each(this.config, function(key, item) {
                    if (item.parent_attribute_element_uid == elementUId) {
                        var el = {
                            'depend_id': item.depend_attribute_element_uid,
                            'value': item.parent_option_id
                        };
                        elements.push(el);
                    }
                });
                return elements;
            }
        }
    }
);
