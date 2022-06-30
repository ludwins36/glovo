<?php

namespace VexShipping\Glovo\Controller\Adminhtml\Glovo;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * MassBlogDelete Class
 */
class CancelarMassaction extends \Magento\Backend\App\Action
{

    protected $glovoModel;
    protected $_massModel;
    protected $_helperData;
    protected $helperdata;

    public function __construct(
        \VexShipping\Glovo\Model\BrandFactory $glovoModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \VexShipping\Glovo\Helper\Data $helperdata,
        \Magento\Ui\Component\MassAction\Filter $massModel,
        Action\Context $context
    ) {
        $this->glovoModel = $glovoModel;
        $this->_massModel = $massModel;
        $this->_scopeConfig = $scopeConfig;
        $this->helperdata = $helperdata;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


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


        
        $Collection = $this->glovoModel->create()->getCollection();
        $model = $this->_massModel;
        $collection = $model->getCollection($Collection);
        $contador = 0;
        try {
           foreach ($collection as $glovo) {

                if(!empty($glovo->getIdGlovo()) && $glovo->getStatus()==2){
                    $order = $objectManager->create('Magento\Sales\Model\Order')->load($glovo->getIdOrder());

                    if($order->getId()){

                        $url = ($ambienteadmin==1)?\VexShipping\Glovo\Api\Variables::URL_TEST:\VexShipping\Glovo\Api\Variables::URL_PRODUCTION;
                            
                        $urlapi = $url."b2b/orders/".$glovo->getIdGlovo()."/cancel";

                        $estimacion = json_encode(array());
                        
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => $urlapi,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_POSTFIELDS => $estimacion,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "POST",
                          CURLOPT_HEADER => 0,
                          CURLOPT_HTTPHEADER => array(
                            "Authorization: Basic ".base64_encode($apiKey.":".$apiSecret),
                            "Content-Type: application/json"
                          )
                        ));

                        $responseaux = curl_exec($curl);

                        curl_close($curl);
                        $response = json_decode($responseaux);
                        
                        $glovo->setData('glovo_log_cancel', $responseaux)->save();

                        if(isset($response->id)){
                            $glovo->setData('status', 0)->save();
                            
                            //$order->setIdglovo($response->id)->save();
                            $contador = $contador + 1;
                        }else{
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __("There was a problem canceling the order to Glovo.")
                            ); 
                        }

                    }

                }
                

            
            }
            
            $this->messageManager->addSuccess(__('%1 orders canceled',$contador));
            
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('vexshipping_glovo/glovo/index');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VexShipping_Glovo::glovo');
    }
}
