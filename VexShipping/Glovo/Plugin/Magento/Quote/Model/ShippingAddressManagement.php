<?php
namespace VexShipping\Glovo\Plugin\Magento\Quote\Model;

class ShippingAddressManagement
{
    protected $_helper;

    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
    }

    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {

        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {

            $address->setCoordenadas($extAttributes->getCoordenadas());
            $address->setTiempo($extAttributes->getTiempo());
        }

        //$address->setCity($cityId);
        
    }
}