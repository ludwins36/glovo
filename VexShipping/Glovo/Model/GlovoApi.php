<?php

namespace VexShipping\Glovo\Model;

use VexShipping\Glovo\Api\ComercioInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\ObjectManager;

class GlovoApi implements ComercioInterface
{

    protected $addressRepository;
    protected $helperdata;
    private $scopeConfig;
    private $assetRepo;
    protected $cart;

    public function __construct(
        Repository $assetRepo,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \VexShipping\Glovo\Helper\Data $helperdata,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->addressRepository = $addressRepository;
        $this->assetRepo = $assetRepo;
        $this->helperdata = $helperdata;
        $this->cart = $cart;
    }

    public function gettracking($customerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        $glovo = $request->getParam('glovo');

        
        $apiKey = $this->helperdata->getApiKey();;
        $apiSecret = $this->helperdata->getApiSecret();
        $ambienteadmin = $this->helperdata->getAmbiente();
        
        $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;
        $urlapi = $url."b2b/orders/".$glovo;

        $curl = curl_init($urlapi);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);                                                                    
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
            "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
            "Content-Type: application/json"
            )                                                                       
        );                                                                                                                                                                                                                                   
            
        $response = curl_exec($curl);
        $response = json_decode($response,true);
        curl_close($curl);

        $urlapi2 = $url."b2b/orders/".$glovo."/tracking";
        $curl2 = curl_init($urlapi2);
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, 0);                                                                    
        curl_setopt($curl2, CURLOPT_HTTPHEADER, array(                                                                          
            "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
            "Content-Type: application/json"
            )                                                                       
        );                                                                                                                                                                                                                                   
            
        $response2 = curl_exec($curl2);
        $response2 = json_decode($response2,true);
        curl_close($curl2);


        $urlapi3 = $url."b2b/orders/".$glovo."/courier-contact";
        $curl3 = curl_init($urlapi3);
        curl_setopt($curl3, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl3, CURLOPT_SSL_VERIFYPEER, 0);                                                                    
        curl_setopt($curl3, CURLOPT_HTTPHEADER, array(                                                                          
            "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
            "Content-Type: application/json"
            )                                                                       
        );                                                                                                                                                                                                                                   
            
        $response3 = curl_exec($curl3);
        $response3 = json_decode($response3,true);
        curl_close($curl3);

        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();

        $responsedata = array("datos_orden"=>$response,"tracking"=>$response2,"contacto"=>$response3, "latitud"=>$latitud, "longitud"=>$longitud);

        return array($responsedata);
    }


    public function verificarPosicion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        $lat = $request->getParam('lat');
        $lng = $request->getParam('lng');

        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();
        $direccion = $this->helperdata->getDireccion();
        $apiKey = $this->helperdata->getApiKey();;
        $apiSecret = $this->helperdata->getApiSecret();
        $ambienteadmin = $this->helperdata->getAmbiente();
        $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;

        $estimacion = array(
            "description" => "Envio por glovo",
            "addresses" => array(
                array(
                    "type"=>"PICKUP",
                    "lat"=>$latitud,
                    "lon"=>$longitud,
                    "label"=>$direccion
                ),
                array(
                    "type"=>"DELIVERY",
                    "lat"=>$lat,
                    "lon"=>$lng,
                    "label"=>"Direccion del cliente"
                )
            )
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cliente = $objectManager->get('\Magento\Framework\HTTP\ZendClientFactory');
        $estimacion = json_encode($estimacion);


        $client = $cliente->create();
        $client->setUri($url.'b2b/orders/estimate');
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept','application/json');
        $client->setHeaders("Authorization","Basic ".base64_encode($apiKey.":".$apiSecret));
        $client->setRawData($estimacion);
        $response= $client->request();
        
        $estadofalse = false;
        $responseactual = json_decode($response->getBody());
        if(isset($responseactual->error)){
            $estadofalse = false;
        }else{
            $estadofalse = true;
        }

        return array(array("status"=>$estadofalse,"texto_error"=>__("Address error"),"texto_error_description"=>__("place the point in the area")));
    }

    
    public function getdataglovo(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();
        $codigo = $this->helperdata->getCodigo();
        $horario = $this->helperdata->getHorario();
        $keyapi = $this->helperdata->getGoogleKey();

        $listapoligonos = $this->obtenerPolylines();

        $points = array();
        $sumw = 0;
        if (is_array($listapoligonos)) {
            $encoded = array_values($listapoligonos);
            for ($i = 0; $i < count($listapoligonos); ++$i) {

                array_push($points, array_chunk($this->decode($listapoligonos[$i]), 2));
                
            }
        }

        $listaitems = $this->cart->getQuote()->getAllItems();
        $horas_preparacion = 0;
        foreach ($listaitems as $item) {
            $producto = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
            if($producto->getTypeId()!="configurable"){
                
                if($producto->getId()){
                    $horas_preparacion = $horas_preparacion + (intval($producto->getData("glovo_preparation_time")) * $item->getQty());
                }
            }
        }

        $reglas = $this->helperdata->getFeriados();
        if ($this->isSerialized($reglas)) {
            $unserializer = $objectManager->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = $objectManager->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        $reglas = $unserializer->unserialize($reglas);
        $feriados = array();
        foreach ($reglas as $key) {
            $feriados[$key["dia"]][$key["mes"]] = true;
        }


        $reglasdias = $this->helperdata->getTrabajos();
        if ($this->isSerialized($reglasdias)) {
            $unserializer = $objectManager->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = $objectManager->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        $t = false;
        $reglasdias = $unserializer->unserialize($reglasdias);
        $listadiasadmin = array();
        foreach ($reglasdias as $key) {
            /*if($diasemana == $key["dia"]){
                if($hora>=$key["hora"] && $hora<$key["hora_fin"]){
                    $t=true;
                }
            }*/
            $listadiasadmin[$key["dia"]][] = array("inicio"=>$key["hora"],"fin"=>$key["hora_fin"]);
            
        }


        $now = new \DateTime();
        $now->modify('+'.$horas_preparacion.' hour');
        $interval = new \DateInterval( 'P1D');
        $period = new \DatePeriod( $now, $interval, 8);
        $sale_data = array();

        $diaactual =  date("d", strtotime('+'.$horas_preparacion.' hours'));//intval(date("d"));
        $mesactual =  date("m", strtotime('+'.$horas_preparacion.' hours'));//intval(date("m"));
        $diasemana =  date("w", strtotime('+'.$horas_preparacion.' hours'));//intval(date("w"));
        $hora = date("H", strtotime('+'.$horas_preparacion.' hours'));//intval(date("H"));
        $contadordias=1;
        $fechaantesposible = "";
        foreach( $period as $day) {

            $diaaux = intval($day->format("d"));
            $mesaux = intval($day->format("m"));
            $diasemanaaux = intval($day->format("w"));
            $horasadminpordia = array();

            if(!isset($feriados[$diaaux][$mesaux])){
                
                if(isset($listadiasadmin[$diasemanaaux])){

                    foreach ($listadiasadmin[$diasemanaaux] as $key) {
                        
                        //$horasadminpordia[][] = "" - "";
                        for ($i=$key['inicio']; $i < $key['fin']; $i++) { 
                            if($diaaux == $diaactual && $mesaux == $mesactual && $i<=$hora){
                                //no mostrar hora antes de la hora actual
                            }else{

                                $horahabi = str_pad(strval($i), 2, "0", STR_PAD_LEFT);
                                $fechaauxiliar = new \DateTime($day->format("Y-m-d")." ".$horahabi.":00:00");
                                $timeunix = intval($fechaauxiliar->getTimestamp())*1000;
                                $horasadminpordia[$timeunix] = str_pad(strval($i), 2, "0", STR_PAD_LEFT).":00 - ".str_pad(strval($i+1), 2, "0", STR_PAD_LEFT).":00";
                            }
                        }

                    }

                    if(count($horasadminpordia)>0 && $contadordias<=3){
                        asort($horasadminpordia);

                        if($fechaantesposible==""){
                            
                            foreach ($horasadminpordia as $key => $value) {
                                $fechaantesposible = $key;
                                break;
                            }
                        }

                        $sale_data[] = array(
                                "label" => $day->format("Y-m-d"),
                                "value" => $horasadminpordia
                            );
                        $contadordias = $contadordias + 1;
                    }
                    
                }
            }
        }
        $horas = array();



        $textos = array(
            "texto_fecha" => __("When would you like to receive your order?"),
            "texto_fecha_true" => __("Send as soon as possible"),
            "texto_fecha_false" => __("Choose when you want to receive your order"),
            "texto_fecha_date" => __("Date"),
            "texto_fecha_hour" => __("Hour approx"),
            "texto_preparacion" => __("Preparation")
        );

        return array(array(
            "listapoligonos"=>$points, "latitud"=>$latitud, "longitud"=>$longitud,
            "horario"=>$horario,"key"=>$keyapi,"fechas_horas"=>$horas,
            "horas_preparacion"=>$horas_preparacion." ".__("hours"),
            "fechas_habilitados" => $sale_data,"textos"=>$textos,"fecha_inicio"=>$fechaantesposible
        ));
    }

    public function decode($string)
    {
        $points = array();
        $index = $i = 0;
        $previous = array(0, 0);
        while ($i < strlen($string)) {
            $shift = $result = 0x00;
            do {
                $bit = ord(substr($string, $i++)) - 63;
                $result |= ($bit & 0x1f) << $shift;
                $shift += 5;
            } while ($bit >= 0x20);

            $diff = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $number = $previous[$index % 2] + $diff;
            $previous[$index % 2] = $number;
            $index++;
            $points[] = $number * 1 / pow(10, 5);//static::$precision);
        }
        return $points;
    }

    public function obtenerPolylines()
    {

        $apiKey = $this->helperdata->getApiKey();;
        $apiSecret = $this->helperdata->getApiSecret();
        $ambienteadmin = $this->helperdata->getAmbiente();


        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();
        $codigo = $this->helperdata->getCodigo();
        $horario = $this->helperdata->getHorario();


        $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;
        $urlapi = $url."b2b/working-areas";
        $codigoCiudad = $codigo;//explode(",", $codigo);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $urlapi,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
            "Content-Type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        
        
        $response = json_decode($response);
        $listapoligonos = array();

        if (!empty($response->error)) {
            return array();
        } else {

            if (is_array($response->workingAreas) || is_object($response->workingAreas)) {
                foreach ($response->workingAreas as $item) {
                    //if(in_array($item->code, $codigoCiudad)){//
                    if ($item->code == $codigoCiudad){
                        $listapoligonos = $item->polygons;

                    }
                }
                
            }
        }


        curl_close($curl);

        return $listapoligonos;

    }
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }


    public function getverificarhora()
    {
        $estadoglovo = true;
        $mensajeerror = "";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        $coordenadas = $request->getParam('coordenadas');
        $tiempo = $request->getParam('tiempo');
        $coordenadasob = explode(",", $coordenadas);
        if(!isset($coordenadasob[1])){
            return array(array("status"=>false,"texto_error"=>__("Glovo is not enabled for this coordinate and/or date.")));
        }

        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();
        $direccion = $this->helperdata->getDireccion();
        $apiKey = $this->helperdata->getApiKey();;
        $apiSecret = $this->helperdata->getApiSecret();
        $ambienteadmin = $this->helperdata->getAmbiente();
        $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;

        $estimacion = array(
            "description" => "Envio por glovo",
            "addresses" => array(
                array(
                    "type"=>"PICKUP",
                    "lat"=>$latitud,
                    "lon"=>$longitud,
                    "label"=>$direccion
                ),
                array(
                    "type"=>"DELIVERY",
                    "lat"=>$coordenadasob[0],
                    "lon"=>$coordenadasob[1],
                    "label"=>"Direccion del cliente"
                )
            )
        );

        $diaactual = intval(date("d"));
        $mesactual = intval(date("m"));
        $diasemana = intval(date("w"));
        $hora = intval(date("H"));

        if(!empty($tiempo) && $tiempo !=0){
            $estimacion['scheduleTime'] = intval($tiempo);

            $datereturn = new \DateTime();
            $datereturn->setTimestamp($tiempo/1000);
            $diaactual = intval($datereturn->format("d"));
            $mesactual = intval($datereturn->format("m"));
            $diasemana = intval($datereturn->format("w"));
            $hora = intval($datereturn->format("H"));
        }

        $reglas = $this->helperdata->getFeriados();
        
        if ($this->isSerialized($reglas)) {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        $reglas = $unserializer->unserialize($reglas);
        
        $feriados = array();

        foreach ($reglas as $key) {
            $feriados[$key["dia"]][$key["mes"]] = true;
        }
        if(isset($feriados[$diaactual][$mesactual])){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is disabled for this day.");
        }


        $reglasdias = $this->helperdata->getTrabajos();
        if ($this->isSerialized($reglasdias)) {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        $t = false;
        $reglasdias = $unserializer->unserialize($reglasdias);
        foreach ($reglasdias as $key) {
            if($diasemana == $key["dia"]){
                if($hora>=$key["hora"] && $hora<$key["hora_fin"]){
                    $t=true;
                }
            }
        }
        if(!$t){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is disabled for this day.");
        }



        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cliente = $objectManager->get('\Magento\Framework\HTTP\ZendClientFactory');
        $estimacion = json_encode($estimacion);


        $client = $cliente->create();
        $client->setUri($url.'b2b/orders/estimate');
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept','application/json');
        $client->setHeaders("Authorization","Basic ".base64_encode($apiKey.":".$apiSecret));
        $client->setRawData($estimacion);
        $response= $client->request();
        
        
        $responseactual = json_decode($response->getBody());
        if(isset($responseactual->error)){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is not enabled for this coordinate and/or date.");
        }else{
            //echo $response->getBody();
            $precioapi = $responseactual->total->amount;
            $precioapi = $precioapi/100;
        }

        return array(array("status"=>$estadoglovo,"texto_error"=>$mensajeerror));
    }

    

}