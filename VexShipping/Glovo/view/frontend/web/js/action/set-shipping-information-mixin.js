define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {  

            // console.log("Enters set-shipping-address-mixin...");

            var shippingAddress = quote.shippingAddress();
        
            
            if (shippingAddress['customAttributes'] === undefined) {
                shippingAddress['customAttributes'] = {};
            }
            //if($('.form-shipping-address [name="custom_attributes[coordenadas]"]') && $('.form-shipping-address [name="custom_attributes[coordenadas]"]').val()) {
                $('.form-shipping-address [name="custom_attributes[coordenadas]"]').val($('#coordenadas_mapa').val());
                shippingAddress['customAttributes']['coordenadas'] = $('#coordenadas_mapa').val();
                $('.form-shipping-address [name="custom_attributes[tiempo]"]').val($('#glovo_fecha').val());
                shippingAddress['customAttributes']['tiempo'] = $('#glovo_fecha').val();
                //alert($('#coordenadas_mapa').val());
            //}

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if (shippingAddress.customAttributes != undefined) {
                $.each(shippingAddress.customAttributes , function( key, value ) {
                    
                    // For display purpose in checkout new and existing addresses
                    shippingAddress['customAttributes'][key] = value;
                    
                    // For rate calculation and saving in db purpose
                    if($.isPlainObject(value)){
                        key = value['attribute_code'];
                        value = value['value'];
                        
                    }
                    
                    shippingAddress['extension_attributes'][key] = value;
                    //shippingAddress['extension_attributes'][value.attribute_code] = value.value;

                });
            }

            return originalAction(messageContainer);
        });
    };
});