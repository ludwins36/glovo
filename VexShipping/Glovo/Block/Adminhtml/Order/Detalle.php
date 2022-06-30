<?php

namespace VexShipping\Glovo\Block\Adminhtml\Order;

use Magento\Sales\Model\Order;  

class Detalle extends \Magento\Backend\Block\Template
{
 
    protected $coreRegistry = null;
    private $scopeConfig;
    protected $helperdata;
    //protected $_template = 'order/view/facturacion_fields.phtml';
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \VexShipping\Glovo\Helper\Data $helperdata,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->helperdata = $helperdata;
        $this->_isScopePrivate = true;  
        parent::__construct($context, $data);
    }


    public function getOrderId() 
    {
        
        $order = $this->coreRegistry->registry('current_order');
        
        $order_id = '';

        if(!$order){
 
        }
        else
        {
            $order_id = $order->getId();
        } 
        
        return $order_id;
    } 
 
    public function getQuoteId() 
    {
        
        $order = $this->coreRegistry->registry('current_order');
        
        $quote_id = '';

        if(!$order){
 
        }
        else
        {
            $quote_id = $order->getQuoteId();
        } 
        
        return $quote_id;
    } 

    public function getShippingDate(){

        $order = $this->coreRegistry->registry('current_order');
        
        if($order){

                return [
                    "fecha" => $order->getShippingAddress()->getData("fechaenvio"),
                    "id" => $order->getIdglovo(),
                    'metodo' => $order->getShippingMethod()
                    ];
            


        }    

        return false;
    }

    public function getDataglovo($glovo){


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
        
        return $responsedata;
    }

    public function key(){
        $key = $this->scopeConfig->getValue('vexsolucionescheckout/general/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $key;
    }
      
}
