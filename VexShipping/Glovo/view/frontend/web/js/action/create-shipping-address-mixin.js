define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            // console.log("Enters create-shipping-address-mixin...");

            if (messageContainer.custom_attributes != undefined) {
                $.each(messageContainer.custom_attributes , function( key, value ) {     
                    
                    messageContainer['custom_attributes'][key] = {'attribute_code':key,'value':value};
    
                });
            }
            
            //if($('.form-shipping-address [name="city"]').val()) {
            //    messageContainer['city'] = $('.form-shipping-address [name="city"] option:selected').text(); 
            //}

            return originalAction(messageContainer);
        });
    };
});