<?php
namespace VexShipping\Glovo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Captcha\Observer\CaptchaStringResolver;

class SalesOrderAfterSave implements ObserverInterface
{

    protected $scopeConfig;
    protected $addressRepository;
    protected $helperdata;
    protected $_commentFactory;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \VexShipping\Glovo\Helper\Data $helperdata,
        \VexShipping\Glovo\Model\BrandFactory $brandFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->addressRepository = $addressRepository;
        $this->helperdata = $helperdata;
        $this->_commentFactory = $brandFactory;
    }

    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/glovo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $order = $observer->getEvent()->getOrder();

        $latitud = $this->helperdata->getLatitud();
        $longitud = $this->helperdata->getLongitud();
        $direccionadmin = $this->helperdata->getDireccion();
        $apiKey = $this->helperdata->getApiKey();
        $apiSecret = $this->helperdata->getApiSecret();
        $ambienteadmin = $this->helperdata->getAmbiente();

        $estadoorden = $this->helperdata->getEstadoPedido();
        $estadoordencambio = $this->helperdata->getCambiarEstado();
        $estadoordennuevo = $this->helperdata->getNuevoEstadoPedido();

        $telefonoadmin = $this->helperdata->getTelefonoContacto();
        $nombreadmin = $this->helperdata->getNombreContacto();
        $referenciaadmin = $this->helperdata->getReferencia();


        $OrderStatus=$order->getStatus();

        


        
        

        $orderShippingAddress = $order->getShippingAddress();
        $coordenadas = $orderShippingAddress->getData('coordenadas');
        $tiempo = $orderShippingAddress->getData('tiempo');
        $street = $orderShippingAddress->getData('street');
        $streetarray = explode("\n", $street);
        $street = trim($streetarray[0]);

        $nombrecliente = $orderShippingAddress->getData('firstname')." ".$orderShippingAddress->getData('lastname');
        $telefonocliente = $orderShippingAddress->getTelephone();
        $referenciacliente = "";
        if(isset($streetarray[1])){
            $referenciacliente .= $streetarray[1];
        }
        if(isset($streetarray[2])){
            $referenciacliente .= ", ".$streetarray[2];
        }
                        
       
        $shipping = $order->getShippingMethod();
            
        if($shipping != 'glovo_glovo'){
            return false;
        }


        $model = $this->_commentFactory->create()->load($order->getId(), 'id_order');
        if(!$model->getId()){
            $model = $this->_commentFactory->create();
            $model->setData('id_order', $order->getId());
            $model->setData('increment_order', $order->getIncrementId());
            $model->setData('status', 1);
            $model->setData('status_order', $OrderStatus);
        }else{
            $model->setData('status_order', $OrderStatus);
        }

        $model->save();


        if($order->getIdglovo()!==null && $order->getIdglovo()!==0 && $order->getIdglovo()!==''){
            return false;
        }

        if($OrderStatus!=$estadoorden){
            return false;
        }



        if(!empty($coordenadas)){

            $latlng = explode(',', $coordenadas);
            if(!isset($latlng[1])){
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The coordinates could not be sent to Glovo.")
                ); 
            }

            $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;
        
            $urlapi = $url."b2b/orders";


            



            $estimacion = array(
                "description" => "Envio por glovo",
                "addresses" => array(
                    array(
                        "type"=>"PICKUP",
                        "lat"=>$latitud,
                        "lon"=>$longitud,
                        "label"=>$direccionadmin,
                        "contactPhone"=> $telefonoadmin,
                        "contactPerson"=> $nombreadmin,
                        "instructions"=> $referenciaadmin
                    ),
                    array(
                        "type"=>"DELIVERY",
                        "lat"=>$latlng[0],
                        "lon"=>$latlng[1],
                        "label"=>$street,
                        "contactPhone"=> $telefonocliente,
                        "contactPerson"=> $nombrecliente,
                        "instructions"=> $referenciacliente
                    )
                )
            );

            if($tiempo!="" && $tiempo!=0){
                $estimacion['scheduleTime'] = intval($tiempo);
            }

 
            $estimacion = json_encode($estimacion);
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $urlapi,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $estimacion,
              CURLOPT_HEADER => 0,
              CURLOPT_HTTPHEADER => array(
                "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
                "Content-Type: application/json"
              )
            ));

            $responseaux = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($responseaux);
            
            $model->setData('glovo_log', $responseaux)->save();

            if(isset($response->id)){
                $model->setData('status', 2)->setData('id_glovo', $response->id)->setData("fecha",date("Y-m-d H:i:s"))->save();
                if($estadoordencambio){
                    $order->setStatus($estadoordennuevo);
                }
                $order->setIdglovo($response->id)->save();
            }else{
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("There was a problem in sending the order to Glovo.")
                ); 
            }

            
        }else{
             throw new \Magento\Framework\Exception\LocalizedException(
                __("The coordinates could not be sent to Glovo.")
            ); 
        }

        
        return $this;
    }

}