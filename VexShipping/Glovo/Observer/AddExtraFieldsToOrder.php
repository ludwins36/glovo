<?php 

namespace VexShipping\Glovo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer; 


class AddExtraFieldsToOrder implements ObserverInterface
{
    
    protected $addressRepository;

    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository){

        $this->addressRepository = $addressRepository;
    }
 
    public function execute(Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
  
        // Quote get shipping address
    
        $quoteShippingAddress = $quote->getShippingAddress();
        $orderShippingAddress = $order->getShippingAddress();

        if($orderShippingAddress){
            $orderShippingAddress->setData(
                "coordenadas",
                $quoteShippingAddress->getData("coordenadas")
            ); 
            $orderShippingAddress->setData(
                "tiempo",
                $quoteShippingAddress->getData("tiempo")
            ); 
        }


        if($quoteShippingAddress->getCustomerAddressId() != '' && is_numeric($quoteShippingAddress->getCustomerAddressId()) ){
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerAddress = $objectManager->create("Magento\Customer\Model\Address")->load($quoteShippingAddress->getCustomerAddressId());
            $customerAddress->setData("coordenadas",$quoteShippingAddress->getData("coordenadas"));
            $customerAddress->setData("tiempo",$quoteShippingAddress->getData("tiempo"))->save();
        }


    }
}
