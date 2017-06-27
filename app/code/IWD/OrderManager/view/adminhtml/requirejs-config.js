var config = {
    map: {
        '*': {
            iwdOrderManagerActions: 'IWD_OrderManager/js/order/view/actions',

            iwdOrderManagerAddress: 'IWD_OrderManager/js/order/view/address',
            iwdOrderManagerAddressBilling : 'IWD_OrderManager/js/order/view/address/billing',
            iwdOrderManagerAddressShipping: 'IWD_OrderManager/js/order/view/address/shipping',

            iwdOrderManagerHistory: 'IWD_OrderManager/js/order/view/history',

            iwdOrderManagerItems: 'IWD_OrderManager/js/order/view/items/items',
            iwdOrderManagerItemsForm: 'IWD_OrderManager/js/order/view/items/form',
            iwdOrderManagerItemsSearch: 'IWD_OrderManager/js/order/view/items/search',

            iwdOrderManagerCoupon: 'IWD_OrderManager/js/order/view/coupon',

            iwdOrderManagerShipping : 'IWD_OrderManager/js/order/view/shipping',
            iwdOrderManagerPayment: 'IWD_OrderManager/js/order/view/payment',

            iwdOrderManagerCustomer : 'IWD_OrderManager/js/order/view/customer',
            iwdOrderManagerOrderInfo: 'IWD_OrderManager/js/order/view/info',

            iwdOrderManagerDesign: 'IWD_OrderManager/js/order/view/design',

            iwdOrderManagerProductView: 'IWD_OrderManager/js/product/edit/stock'
        }
    },

    config: {
        mixins: {
            'Magento_Ui/js/grid/data-storage': {
                'IWD_OrderManager/js/ui/grid/data-storage':true
            }
        }
    }
};
