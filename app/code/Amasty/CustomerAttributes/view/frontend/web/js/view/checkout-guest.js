define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'uiRegistry'
    ],
    function (
        $,
        ko,
        _,
        Component,
        quote,
        registry

    ) {
        return Component.extend({
            isVisible    : ko.observable(false),
            dependsToShow: [],

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                var self = this;

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.on('shippingAddress.custom_attributes', function (customerAttributes) {
                        self.saveCustomerAttributesToQuote(customerAttributes);
                    }.bind(self));
                });

                return this;
            },
            initObservable: function () {
                this._super();
                this.elems.subscribe(function(childElements) {
                    childElements.map(function(element) {
                        // var element = registry.get(elementName);
                        if(element && _.isFunction(element.checkDependencies)) {
                            element.checkDependencies();
                        }
                    }.bind(this));
                    this.dependsToShow = [];
                }.bind(this));

                return this;
            },
            saveCustomerAttributesToQuote: function (customerAttributes) {
                var shippingAddress = quote.shippingAddress();
                if (shippingAddress) {
                    $.each(customerAttributes, function(index, value) {
                        if (typeof(value) == 'object') {
                            customerAttributes[index] = customerAttributes[index].join(',');
                        }
                        var element = this.getChild(index);
                        if (element && !element.visible()) {
                            customerAttributes[index] = void(0);
                        }
                    }.bind(this));
                    shippingAddress.custom_attributes = $.extend(
                        {}, shippingAddress.custom_attributes, customerAttributes
                    );
                    quote.shippingAddress(shippingAddress);
                }
            }
        });
    }
);
