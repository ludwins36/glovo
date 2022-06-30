/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor',
    'jquery'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor, $) {
    'use strict';

    return {
        /**
         * Get shipping rates for specified address.
         * @param {Object} address
         */
        getRates: function (address) {
            var cache, serviceUrl, payload;
            
            console.log(address);
            if (typeof address.customAttributes != undefined) {
                
                $.each(address.customAttributes , function( key, value ) {     
                    
                    if($("#coordenadas_mapa").length>0 ){
                        
                        if(typeof value.attribute_code != undefined && value.attribute_code=="coordenadas"){
                            address.customAttributes[key] = {'attribute_code':"coordenadas",'value':$("#coordenadas_mapa").val()};
                        }else if(key=="coordenadas"){
                            address.customAttributes[key] = {'attribute_code':"coordenadas",'value':$("#coordenadas_mapa").val()};
                        }


                        if(typeof value.attribute_code != undefined && value.attribute_code=="tiempo"){
                            address.customAttributes[key] = {'attribute_code':"tiempo",'value':$("#glovo_fecha").val()};
                        }else if(key=="tiempo"){
                            address.customAttributes[key] = {'attribute_code':"tiempo",'value':$("#glovo_fecha").val()};
                        }

                    }else{
                        if(typeof value.attribute_code != undefined && value.attribute_code=="coordenadas"){
                            address.customAttributes[key] = {'attribute_code':"coordenadas",'value':"0,0"};
                        }else if(key=="coordenadas"){
                            address.customAttributes[key] = {'attribute_code':"coordenadas",'value':"0,0"};
                        }


                        if(typeof value.attribute_code != undefined && value.attribute_code=="tiempo"){
                            address.customAttributes[key] = {'attribute_code':"tiempo",'value':0};
                        }else if(key=="tiempo"){
                            address.customAttributes[key] = {'attribute_code':"tiempo",'value':0};
                        }
                    }

                    /*if(typeof value.attribute_code != undefined && value.attribute_code=="coordenadas" && $("#coordenadas_mapa").length>0 && $("#coordenadas_mapa").val()!=""){
                        address.customAttributes[key] = {'attribute_code':"coordenadas",'value':$("#coordenadas_mapa").val()};
                    }*/
                    
                });
            }

            console.log(address);


            shippingService.isLoading(true);
            cache = rateRegistry.get(address.getCacheKey());
            serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote);
            payload = JSON.stringify({
                    address: {
                        'street': address.street,
                        'city': address.city,
                        'region_id': address.regionId,
                        'region': address.region,
                        'country_id': address.countryId,
                        'postcode': address.postcode,
                        'email': address.email,
                        'customer_id': address.customerId,
                        'firstname': address.firstname,
                        'lastname': address.lastname,
                        'middlename': address.middlename,
                        'prefix': address.prefix,
                        'suffix': address.suffix,
                        'vat_id': address.vatId,
                        'company': address.company,
                        'telephone': address.telephone,
                        'fax': address.fax,
                        'custom_attributes': address.customAttributes,
                        'save_in_address_book': address.saveInAddressBook
                    }
                }
            );

            /*if (cache) {
                shippingService.setShippingRates(cache);
                shippingService.isLoading(false);
            } else {*/
                storage.post(
                    serviceUrl, payload, false
                ).done(function (result) {
                    rateRegistry.set(address.getCacheKey(), result);
                    shippingService.setShippingRates(result);

                    let auxlixar = quote.shippingMethod() ?
                        quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                        null;
                    
                    if(auxlixar!=null && auxlixar=="glovo_glovo"){
                        $("#seleccionarglovo").show();
                    }else{
                        $("#seleccionarglovo").hide();
                    }

                }).fail(function (response) {
                    shippingService.setShippingRates([]);
                    errorProcessor.process(response);
                }).always(function () {
                    shippingService.isLoading(false);
                });
            /*}*/
        }
    };
});
