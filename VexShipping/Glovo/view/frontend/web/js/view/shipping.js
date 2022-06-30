/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',//customer-address',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t,
    ratesUbigeo
) {
    'use strict';

    var popUp = null;

    let end;
    let directionsDisplay;
    let destination=null;
    let initf;

    function getRootUrl() {

        return window.location.origin 
                        ? window.location.origin + '/'
                        : window.location.protocol + '/' + window.location.host + '/';
    
    }



    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),
        mapGeoCode: null,
        geocoder: null,
        verificareventoend : null,
        marcadorDelivery : null,
        marcadorCoordenadas : ko.observable(),

        fechaRequerido : ko.observable(false),
        fechaelegida : ko.observable(''),
        chackeventfecha : ko.observable(true),
        fechafromatooriginal: ko.observable(''),
        definirfecha : ko.observable(false),
        seleccionarglovo : ko.observable(false),
        verificarglovo : ko.observable(false),

        textofecha : ko.observable("When would you like to receive your order?"),
        textoslide : ko.observable("Send as soon as possible"),

        textoslidetrue : ko.observable("Choose when you want to receive your order"),
        textoslidefalse : ko.observable("Send as soon as possible"),
        fechastexto : ko.observable("Date"),
        horastexto : ko.observable("Hour approx"),

        textopreparacion : ko.observable("Preparation"),
        textopreparacionhoras : ko.observable("0 hours"),

        fechas: ko.observableArray(),
        fechasvalue: ko.observable(),
        horas: ko.observableArray(),
        horasvalue: ko.observable(),
        horastodos: ko.observableArray(),

        horareciente: ko.observable(),

        latitudtienda: null,
        longitudtienda: null,
        keygoogle: null, 
        infoError: null,
        markertienda: null,

        //texto : ko.observable(false),

        /**
         * @return {exports}
         */

         

        dibujarPolylines: function(arrayPuntos,mapaf) {
            var flightPlanCoordinates = [];

            for (var i = 0; i < arrayPuntos.length; i++) {
                flightPlanCoordinates.push({
                    lat: arrayPuntos[i][0],
                    lng: arrayPuntos[i][1]
                });
            }
            flightPlanCoordinates.push({
                lat: arrayPuntos[0][0],
                lng: arrayPuntos[0][1]
            });

            var flightPath = new google.maps.Polygon({
                path: flightPlanCoordinates,
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 0.2,
                strokeWeight: 1,
                fillColor: "#FF0000",
                fillOpacity: 0.4
            });

            flightPath.setMap(mapaf);
        },

        addAddressMap: function(address,googleKey,map,lataux, lngaux) {
            
            let self = this;
            let filename =
                "https://maps.googleapis.com/maps/api/geocode/json?key=" +
                googleKey +
                "&address=" +
                encodeURI(address) +
                "&sensor=false";

            fetch(filename)
                .then(resp => resp.text())
                .then(function (data) {
                    let info = JSON.parse(data);
                    
                    if (info.results.length == 0) {
                        return;
                    }

                    let coor = info.results[0].geometry.location;
                    end = new google.maps.LatLng(
                        parseFloat(coor.lat),
                        parseFloat(coor.lng)
                    );

                    let directionsService = new google.maps.DirectionsService();

                    if (destination != undefined) {
                        destination.setMap(null);
                        directionsDisplay.setMap(null);
                    }

                    directionsDisplay = new google.maps.DirectionsRenderer({
                        draggable: true,
                        map: map,
                        suppressMarkers: true
                    });

                    destination = new google.maps.Marker({
                        draggable: true,
                        map: map,
                        position: end,
                        //label: "TU",
                        title: "destinatario"
                    });

                    
                    destination.addListener("dragend", event => {
                        let latlng = new google.maps.LatLng(lataux, lngaux);
                        $("#coordenadas_mapa").val(event.latLng.lat()+","+event.latLng.lng());
                        self.displayRoute(
                            latlng,
                            event.latLng,
                            directionsService,
                            directionsDisplay,
                            destination,
                            false
                        );

                    });
                    let latlng = new google.maps.LatLng(lataux, lngaux);
                    $("#coordenadas_mapa").val(coor.lat+","+coor.lng);
                    self.displayRoute(latlng, end, directionsService, directionsDisplay,destination,true);
                    
                });
        },

        displayRoute: function(origin, destination, service, display,marker,actualizarubigeo) {
            let self = this;

            service.route({
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.DirectionsTravelMode.DRIVING,
                    unitSystem: google.maps.UnitSystem.METRIC
                },
                function (response, status) {
                    
                    if(actualizarubigeo){
                        self.refrescarubigeoship();
                    }

                    if (status === "OK") {
                        display.setDirections(response);
                        let location = directionsDisplay.getDirections();
                        /*if (typeof location !== 'undefined' && CheckGlovoData) {
                            location = location.routes[0].legs[0].end_address;
                            address.value = location;
                        }*/

                    }
                }
            );

            if(self.infoError!=null){
                self.infoError.close();
            }
            
            $.ajax({
                url: getRootUrl()+'rest/default/V1/verificarPosicionGlovo?lat='+destination.lat()+'&lng='+destination.lng(),
                //data: JSON.stringify({}),
                showLoader: true,
                type: 'GET',
                dataType: 'json',
                context: this, 
                async : false,
                beforeSend: function(request) {
                    request.setRequestHeader('Content-Type', 'application/json');
                },
                success: function(response){
                    self.errorValidationMessage(false);
                    if(response[0].status){
                        self.infoError = null;
                        self.verificarglovo(true);
                    }else{
                        let contentString = '<div style="text-align:center;"><div style="color: red;"><b>' +
                                    response[0].texto_error +
                                    "</b></div><div>"+response[0].texto_error_description+"</div></div>"
                        self.infoError = new google.maps.InfoWindow({
                            content: contentString
                        });
                        self.infoError.open(self.mapGeoCode, marker);
                        self.verificarglovo(false);
                    }

                  

                },

                complete: function () { 
                     
                }
            });


            
        },
        initialize: function () {
            initf = this;
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();

            if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    10
                );
            }
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    checkoutData.setShippingAddressFromData(shippingAddrsData);
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            $(document).ready(function(){

                


                $.ajax({
                    url: getRootUrl()+'rest/default/V1/getDataGlovo',
                    data: JSON.stringify({}),
                    showLoader: true,
                    type: 'GET',
                    dataType: 'json',
                    context: this, 
                    async : false,
                    beforeSend: function(request) {
                        request.setRequestHeader('Content-Type', 'application/json');
                    },
                    success: function(response){
                 
                        
                        self.textofecha(response[0].textos.texto_fecha);
                        self.textoslide(response[0].textos.texto_fecha_true);
                        self.textoslidetrue(response[0].textos.texto_fecha_false);
                        self.textoslidefalse(response[0].textos.texto_fecha_true);
                        self.fechastexto(response[0].textos.texto_fecha_date);
                        self.horastexto(response[0].textos.texto_fecha_hour);
                        self.textopreparacion(response[0].textos.texto_preparacion);
                        self.textopreparacionhoras(response[0].horas_preparacion);
                        self.horareciente(response[0].fecha_inicio);

                        self.latitudtienda = response[0].latitud;
                        self.longitudtienda = response[0].longitud;
                        self.keygoogle = response[0].key;

                        let fechasTmp = [];
                        let fechas_habilitados = response[0].fechas_habilitados;

                        for(let i = 0; i < fechas_habilitados.length; i++){
                              
                            fechasTmp.push({    
                                'id' : fechas_habilitados[i].label,
                                'text': fechas_habilitados[i].label 
                            });

                            let horasTmp = [];
                            self.horastodos[fechas_habilitados[i].label] = []; 

                            $.each(fechas_habilitados[i].value, function(is, item) {
                                self.horastodos[fechas_habilitados[i].label].push({    
                                    'id' : is,
                                    'text': item
                                });
                            });
                    
                        }
                        self.fechas(fechasTmp);


                        var tiempocarga = setInterval(function(){

                            if($("#mapaGeoDecode").length >0){
                                clearInterval(tiempocarga);

                                $("#contenedor-mapa").show();
                                self.mapGeoCode = new google.maps.Map(document.getElementById('mapaGeoDecode'), {
                                      zoom: 11,
                                      center: {lat: parseFloat(self.latitudtienda), lng: parseFloat(self.longitudtienda)}
                                });
                                
                                let imagentienda = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                                if(typeof window.imgpathtiendaglovo != "undefined" && window.imgpathtiendaglovo!=""){
                                    imagentienda = window.imgpathtiendaglovo;
                                }

                                //tienda almacen
                                self.markertienda = new google.maps.Marker({
                                      map: self.mapGeoCode,
                                      icon: imagentienda,
                                      position: { lat: parseFloat(self.latitudtienda), lng: parseFloat(self.longitudtienda) }
                                });
                                var contentString = "<div style='text-align:center;'><div style='color: #3ea443;font-weight: bold;font-size: 14px;margin-bottom: 2px;'>"+self.textopreparacion()+"</div><div>"+self.textopreparacionhoras()+"</div></div>";
                                var infowindow = new google.maps.InfoWindow({
                                    content: contentString
                                });

                                infowindow.open(self.mapGeoCode, self.markertienda);


                                //dibujar poligonos
                                for (var i = 0; i < response[0].listapoligonos.length; i++) {
                                    
                                    self.dibujarPolylines(response[0].listapoligonos[i], self.mapGeoCode);
                                }

                                //input
                                if(quote.shippingAddress()!=null){
                                    let calle = quote.shippingAddress().street ? quote.shippingAddress().street[0] : '';
                                    if(calle!=""){
                                        setTimeout(function(){
                                            self.addAddressMap(calle,self.keygoogle,self.mapGeoCode, parseFloat(self.latitudtienda), parseFloat(self.longitudtienda));
                                        },2000);
                                        
                                    }
                                }

                                if(response[0].horario==1){
                                    self.definirfecha(true);
                                }

                                
                            }

                        }, 1500);


                        var tiempocargaautocomplete = setInterval(function(){
                            if($('input[name="street[0]"]').length >0){
                                clearInterval(tiempocargaautocomplete);
                                let address2 = document.getElementsByName("street[0]")[0];
                                let auto = new google.maps.places.Autocomplete(address2);
                                auto.addListener("place_changed", event => {

                                    self.addAddressMap(address2.value,self.keygoogle,self.mapGeoCode, parseFloat(self.latitudtienda), parseFloat(self.longitudtienda));
                                });
                            }
                        },1500);

                      

                    },

                    complete: function () { 
                         
                    }
                });






                
            });


            quote.shippingAddress.subscribe( (newValue) => {
                console.log(newValue)
                if( newValue.street ){

                    this.addAddressMap(newValue.street[0],self.keygoogle,self.mapGeoCode, parseFloat(self.latitudtienda), parseFloat(self.longitudtienda));
                    //direccion = newValue.street[0];
                    //departamento_interior = newValue.street[1];
                    //referencia = newValue.street[2];
                }
                        

            });

            this.chackeventfecha.subscribe(function (chackeventfecha) {
                self.errorValidationMessage(false);
                if(chackeventfecha){
                    this.fechaRequerido(false);
                    this.textoslide(this.textoslidefalse());
                    $("#glovo_fecha").val(0);//$("#glovo_fecha").val(this.horareciente());

                }else{
                    this.fechaRequerido(true);
                    this.textoslide(this.textoslidetrue());
                    $("#glovo_fecha").val(self.horasvalue().id);
                    console.log("Cambio hora:"+self.horasvalue().id);


                }

                
                //self.refrescarCostosPorDistancia();
            }, this);
            this.fechasvalue.subscribe(function(newValue) {
                self.errorValidationMessage(false);
                self.horas(self.horastodos[newValue.id]);
            });
            this.horasvalue.subscribe(function(newValue) {
                self.errorValidationMessage(false);
                //self.fechasvalue().id
                if(self.chackeventfecha()){
                    $("#glovo_fecha").val(0);
                }else{
                    $("#glovo_fecha").val(newValue.id);
                }
                
                console.log(self.chackeventfecha());
                console.log("Cambio hora:"+newValue.id);
                
            });




            return this;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');

                addressData.street[0] = $('input[name="street[0]"]').val();

                if(typeof addressData.custom_attributes != "undefined" && typeof addressData.custom_attributes.coordenadas != "undefined"){
                    if(typeof addressData.custom_attributes.coordenadas.value != "undefined"){
                        addressData.custom_attributes.coordenadas.value = $('#coordenadas_mapa').val();
                    }else{
                        addressData.custom_attributes.coordenadas = $('#coordenadas_mapa').val();
                    }
                    addressData.custom_attributes.coordenadas = $('#coordenadas_mapa').val();
                }

                if(typeof addressData.custom_attributes != "undefined" && typeof addressData.custom_attributes.tiempo != "undefined"){
                    if(typeof addressData.custom_attributes.tiempo.value != "undefined"){
                        addressData.custom_attributes.tiempo.value = $('#glovo_fecha').val();
                    }else{
                        addressData.custom_attributes.tiempo = $('#glovo_fecha').val();
                    }
                    addressData.custom_attributes.tiempo = $('#glovo_fecha').val();
                }
                
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod,e) {

            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            
            if(shippingMethod!=null && shippingMethod.carrier_code=="glovo" && shippingMethod.method_code=="glovo"){
                $("#seleccionarglovo").show();

                if(destination!=null){
                    setTimeout(function() {
                        var bounds = new google.maps.LatLngBounds();
                        bounds.extend(initf.markertienda.position);
                        bounds.extend(destination.position);
                        initf.mapGeoCode.fitBounds(bounds);
                    },800);
                    
                }
                

            }else{
                $("#seleccionarglovo").hide();
            }

            console.log();

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            console.log("validacion2")
            if (this.validateShippingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                    }
                );
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                country = registry.get(this.parentName + '.shippingAddress.shipping-address-fieldset.country_id'),
                countryIndexedOptions = country.indexedOptions,
                option = countryIndexedOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

                
            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }
            
            if(quote.shippingMethod()){
                let metodoenvioglovo = quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'];
                if(metodoenvioglovo=="glovo_glovo"){

                    if(!this.verificarglovo()){
                        this.errorValidationMessage(
                            $t('No se puede enviar por Glovo a esa zona.')
                        );

                        return false;
                    }else{
                        //verificar horario
                        let statusk1 = false;
                        let msgk1 = "";
                        $.ajax({
                            url: getRootUrl()+'rest/default/V1/getVerificarHoraGlovo?coordenadas='+$('#coordenadas_mapa').val()+'&tiempo='+$('#glovo_fecha').val(),
                            //data: JSON.stringify({}),
                            showLoader: true,
                            type: 'GET',
                            dataType: 'json',
                            context: this, 
                            async : false,
                            beforeSend: function(request) {
                                request.setRequestHeader('Content-Type', 'application/json');
                            },
                            success: function(response){
                         
                                if(response[0].status){
                                }else{
                                    msgk1 = response[0].texto_error;
                                    statusk1 = true;
                                }

                              

                            },

                            complete: function () { 
                                 
                            }
                        });

                        if(statusk1){
                            this.errorValidationMessage(
                                $t(msgk1)
                            );

                            return false;
                        }

                    }

                }
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                shippingAddress['street'][0] = $('input[name="street[0]"]').val();
                console.log($('input[name="street[0]"]').val())
                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').focus();

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        },





        refrescarubigeoship: function(){
              
            let address = quote.shippingAddress();
            console.log(address);
            
            ratesUbigeo.getRates(address);

            /*let auxlixar = quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;

            if(auxlixar!=null && auxlixar=="glovo_glovo"){
                $("#seleccionarglovo").show();
            }else{
                $("#seleccionarglovo").hide();
            }*/
            
        },
        convertunix: function(input){
            input= input+":00";
            var parts = input.trim().split(' ');
            var date = parts[0].split('-');
            var time = (parts[1] ? parts[1] : '00:00:00').split(':');

            // NOTE:: Month: 0 = January - 11 = December.
            var d = new Date(date[0],date[1]-1,date[2],time[0],time[1],time[2]);
            return d.getTime() / 1000;
        },
        zfill: function(number, width) {
            var numberOutput = Math.abs(number); /* Valor absoluto del número */
            var length = number.toString().length; /* Largo del número */ 
            var zero = "0"; /* String de cero */  
            
            if (width <= length) {
                if (number < 0) {
                     return ("-" + numberOutput.toString()); 
                } else {
                     return numberOutput.toString(); 
                }
            } else {
                if (number < 0) {
                    return ("-" + (zero.repeat(width - length)) + numberOutput.toString()); 
                } else {
                    return ((zero.repeat(width - length)) + numberOutput.toString()); 
                }
            }
        }




    });
});
