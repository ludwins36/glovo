<?php
namespace VexShipping\Glovo\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\ObjectManager;

class Glovo extends AbstractCarrier implements CarrierInterface
{
    const CODE = 'glovo';
    private $scopeConfig;

    protected $_code = self::CODE;
    protected $_session;
    protected $checkoutSession;
    protected $request;
    protected $addressRepository;

    private $_rateResultFactory;
    private $_rateMethodFactory;
    private $trackStatusFactory;
    private $_addressFactory;

    public $anchopaquete = 40;
    public $largopaquete = 30;
    public $altopaquete = 40;
    public $pesopaquete = 9;
    public $numeroarticulos = 10;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Webapi\Rest\Request $request,
        CheckoutSession $checkoutSession,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->request = $request;
        $this->_session = $session;
        $this->addressRepository = $addressRepository;
        $this->checkoutSession = $checkoutSession;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->_addressFactory = $addressFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
 
    public function getAllowedMethods()
    {
        return [
            $this->_code => $this->getConfigData('name')
        ];
    }
 

    public function collectRates(RateRequest $request)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/glovo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        $result = $this->_rateResultFactory->create();


        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $estadoglovo = true;
        $mensajeerror = "";

        $url = ($this->getConfigData('glovo_w4/ambiente')==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;

        //echo print_r($this->obtenercoordenadas());die;
        $coordenadasob = explode(",", $this->obtenercoordenadas());
        $tiempo = $this->obtenertiempo();

        if(!isset($coordenadasob[1])){
            return false;
        }


       $estimacion = array(
            "description" => "Envio por glovo",
            "addresses" => array(
                array(
                    "type"=>"PICKUP",
                    "lat"=>$this->getConfigData('glovo_w1/latitud'),
                    "lon"=>$this->getConfigData('glovo_w1/longitud'),
                    "label"=>$this->getConfigData('glovo_w1/direccion')
                ),
                array(
                    "type"=>"DELIVERY",
                    "lat"=>$coordenadasob[0],
                    "lon"=>$coordenadasob[1],
                    "label"=>$request->getDestStreet()
                )
            )
        );

       if(!empty($tiempo) && $tiempo !=0){
        $estimacion['scheduleTime'] = intval($tiempo);
       }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cliente = $objectManager->get('\Magento\Framework\HTTP\ZendClientFactory');
        $estimacion = json_encode($estimacion);


        $client = $cliente->create();
        $client->setUri($url.'b2b/orders/estimate');
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept','application/json');
        $client->setHeaders("Authorization","Basic ".base64_encode($this->getConfigData('glovo_w4/apiKey').":".$this->getConfigData('glovo_w4/apiSecret')));
        $client->setRawData($estimacion);
        $response= $client->request();
        


        $precioapi = 0;
        $responseactual = json_decode($response->getBody());
        if(isset($responseactual->error)){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is not enabled for this coordinate and/or date.");
        }else{
            //echo $response->getBody();
            $precioapi = $responseactual->total->amount;
            $precioapi = $precioapi/100;
        }

        
            


        $diaactual = intval(date("d"));
        $mesactual = intval(date("m"));
        $diasemana = intval(date("w"));
        $hora = intval(date("H"));

        if(!empty($tiempo) && $tiempo !=0){
           
            $datereturn = new \DateTime();
            $datereturn->setTimestamp($tiempo/1000);
            $diaactual = intval($datereturn->format("d"));
            $mesactual = intval($datereturn->format("m"));
            $diasemana = intval($datereturn->format("w"));
            $hora = intval($datereturn->format("H"));
        }


        $tipoprecio = $this->getConfigData('tipo_precio');
        $precio = 0;

        $reglas = $this->getConfigData('glovo_w1/commands_list');
        
        if ($this->isSerialized($reglas)) {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        $reglas = $unserializer->unserialize($reglas);

        
        /*$feriados = array();

        foreach ($reglas as $key) {
            $feriados[$key["dia"]][$key["mes"]] = true;
        }

        if(isset($feriados[$diaactual][$mesactual])){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is disabled for this day.");
        }*/


        $items = $request->getAllItems();
        $totalkilos = 0;
        $totalancho = 0;
        $totallargo = 0;
        $totalalto = 0;
        $totalproductos = 0;
        foreach ($items as $item) {
            $producto = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
            if($producto->getTypeId()!="configurable"){
                $productWeight = $item->getWeight() * $item->getQty();
                $totalkilos = $totalkilos + $productWeight;

                $totalanchoaux = floatval($producto->getData("glovo_width")) * $item->getQty();
                $totalaltoaux = floatval($producto->getData("glovo_height")) * $item->getQty();
                $totallargoaux = floatval($producto->getData("glovo_long")) * $item->getQty();

                $totalancho = $totalancho + $totalanchoaux;
                $totallargo = $totallargo + $totallargoaux;
                $totalalto = $totalalto + $totalaltoaux;
                $totalproductos = $totalproductos + $item->getQty();
            }
        }



        if($totalancho>$this->anchopaquete || $totalalto>$this->altopaquete || $totallargo>$this->largopaquete || $totalproductos > $this->numeroarticulos || $totalkilos > $this->pesopaquete){
            $estadoglovo = false;
            $mensajeerror = __("Glovo is not enabled, the products exceed the shipping capacity.");
        }


        /*$reglasdias = $this->getConfigData('glovo_w1/commands_list_work');
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
        }*/
        


        if($tipoprecio==0){
            $precio = 0;
        }else if($tipoprecio==1){
            $precio = $precioapi;
        }else if($tipoprecio==2){
            $precio = $this->getConfigData('precio');
        }
        

        if($estadoglovo){
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setCarrierTitle($this->getConfigData('titulo'));
            $method->setPrice($precio);
            $method->setCost($precio);
            $result->append($method);
        }else{
            
             $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('titulo'),
                        'error_message' => $mensajeerror,
                    ],
                ]
            );
            $result->append($error);
        }
       

        
        
        

        return $result;
    }

    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

    private function obtenercoordenadas(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $formpostdata   = json_decode(file_get_contents('php://input'), true);
        $_destProvince = "";
        $valorcustom = false;
        $valorcustom2 = false;

        if(isset($formpostdata['address']['custom_attributes'])){
            if(is_array($formpostdata['address']['custom_attributes'])){
                foreach ($formpostdata['address']['custom_attributes'] as $key) {
                    
                    if(isset($key['attribute_code']) && $key['attribute_code'] == "coordenadas"){
                        $valorcustom = $key['value'];
                    }
                }
            }
            
        }

        if(isset($formpostdata['addressInformation']['shipping_address']['customAttributes'])){
            if(is_array($formpostdata['addressInformation']['shipping_address']['customAttributes'])){
                foreach ($formpostdata['addressInformation']['shipping_address']['customAttributes'] as $key) {
                    
                    if(isset($key['attribute_code']) && $key['attribute_code'] == "coordenadas"){
                        $valorcustom2 = $key['value'];
                    }
                }
            }
            
        }


        if ($valorcustom!==false) {
            $_destProvince = $valorcustom;
        
        //Called when setting shipping information (shipping-information)
        } elseif (isset($formpostdata['address']['custom_attributes']['coordenadas'])) {
            $_destProvince = $formpostdata['address']['custom_attributes']['coordenadas'];

            if(!empty($_destProvince['value'])){
                $_destProvince = $_destProvince['value'];
            }
        
        //Called when setting shipping information (shipping-information)
        } elseif ($valorcustom2!==false) {
            
            $_destProvince = $valorcustom2;

        } elseif (isset($formpostdata['addressInformation']['shipping_address']['customAttributes']['coordenadas'])) {
            
            //Check if address Id is set...
            /*if(!empty($formpostdata['addressInformation']['shipping_address']['customerAddressId'])){
                $addressId      = $formpostdata['addressInformation']['shipping_address']['customerAddressId'];
                $destAddress    = $this->_addressFactory->create()->load($addressId);
                $_destProvince  = $destAddress->getCoordenadas();

            } else {*/

                $_destProvince = $formpostdata['addressInformation']['shipping_address']['customAttributes']['coordenadas'];

                //Checking if $_destProvince is an array
                if(!empty($_destProvince['value'])){

                    $_destProvince = $_destProvince['value'];

                }

            //}           

        //Called when setting payment information (payment-information)
        } elseif (isset($formpostdata['cartId'])) {

            //Get province from shipping address in quote
            $quote              =   $objectManager->get('\Magento\Checkout\Model\Session')->getQuote();
            $_destProvince      =   $quote->getShippingAddress()->getCoordenadas();

        //Called when estimating shipping rates for saved addresses (estimate-shipping-methods-by-address-id)
        } elseif (isset($formpostdata['addressId'])) {
            $destAddress    = $this->_addressFactory->create()->load($formpostdata['addressId']);
            $_destProvince  = $destAddress->getCoordenadas();
        } 


        return $_destProvince;
    }



    private function obtenertiempo(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $formpostdata   = json_decode(file_get_contents('php://input'), true);
        $_destProvince = "";
        $valorcustom = false;
        $valorcustom2 = false;
        
        if(isset($formpostdata['address']['custom_attributes'])){
            if(is_array($formpostdata['address']['custom_attributes'])){
                foreach ($formpostdata['address']['custom_attributes'] as $key) {
                    
                    if(isset($key['attribute_code']) && $key['attribute_code'] == "tiempo"){
                        $valorcustom = $key['value'];
                    }
                }
            }
            
        }

        if(isset($formpostdata['addressInformation']['shipping_address']['customAttributes'])){
            if(is_array($formpostdata['addressInformation']['shipping_address']['customAttributes'])){
                foreach ($formpostdata['addressInformation']['shipping_address']['customAttributes'] as $key) {
                    
                    if(isset($key['attribute_code']) && $key['attribute_code'] == "tiempo"){
                        $valorcustom2 = $key['value'];
                    }
                }
            }
            
        }


        if ($valorcustom!==false) {
            $_destProvince = $valorcustom;
        
        //Called when setting shipping information (shipping-information)
        } elseif (isset($formpostdata['address']['custom_attributes']['tiempo'])) {
            $_destProvince = $formpostdata['address']['custom_attributes']['tiempo'];

            if(!empty($_destProvince['value'])){
                $_destProvince = $_destProvince['value'];
            }
        
        //Called when setting shipping information (shipping-information)
        } elseif ($valorcustom2!==false) {
            
            $_destProvince = $valorcustom2;

        } elseif (isset($formpostdata['addressInformation']['shipping_address']['customAttributes']['tiempo'])) {
            
            //Check if address Id is set...
            /*if(!empty($formpostdata['addressInformation']['shipping_address']['customerAddressId'])){
                $addressId      = $formpostdata['addressInformation']['shipping_address']['customerAddressId'];
                $destAddress    = $this->_addressFactory->create()->load($addressId);
                $_destProvince  = $destAddress->getTiempo();

            } else {*/

                $_destProvince = $formpostdata['addressInformation']['shipping_address']['customAttributes']['tiempo'];

                //Checking if $_destProvince is an array
                if(!empty($_destProvince['value'])){

                    $_destProvince = $_destProvince['value'];

                }

            //}           

        //Called when setting payment information (payment-information)
        } elseif (isset($formpostdata['cartId'])) {

            //Get province from shipping address in quote
            $quote              =   $objectManager->get('\Magento\Checkout\Model\Session')->getQuote();
            $_destProvince      =   $quote->getShippingAddress()->getTiempo();

        //Called when estimating shipping rates for saved addresses (estimate-shipping-methods-by-address-id)
        } elseif (isset($formpostdata['addressId'])) {
            $destAddress    = $this->_addressFactory->create()->load($formpostdata['addressId']);
            $_destProvince  = $destAddress->getTiempo();
        } 


        return $_destProvince;
    }

    
}