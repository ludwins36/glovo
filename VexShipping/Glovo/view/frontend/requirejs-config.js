var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-billing-address': {
                'VexShipping_Glovo/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'VexShipping_Glovo/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'VexShipping_Glovo/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'VexShipping_Glovo/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'VexShipping_Glovo/js/action/set-billing-address-mixin': true
            },
        }
    },

    map: {
        "*": {
            'Magento_Checkout/js/view/shipping': 'VexShipping_Glovo/js/view/shipping',
            "Magento_Checkout/js/model/shipping-save-processor/default" : "VexShipping_Glovo/js/model/shipping-save-processor/default",
            //'Magento_Checkout/template/shipping-address/form.html': 'VexShipping_Glovo/template/form.html',
            'Magento_Checkout/template/shipping.html': 'VexShipping_Glovo/template/form.html',
            'Magento_Checkout/js/model/address-converter': 'VexShipping_Glovo/js/model/address-converter',
            'Magento_Checkout/js/model/shipping-rate-processor/customer-address': 'VexShipping_Glovo/js/model/shipping-rate-processor/customer-address',
            'Magento_Checkout/js/model/shipping-rate-processor/new-address': 'VexShipping_Glovo/js/model/shipping-rate-processor/new-address',
        }
    }
};