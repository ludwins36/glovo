<?php

namespace VexShipping\Glovo\Plugin\Magento\Quote\Model;

class ShippingInformationManagementPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

 
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {

        $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
        $coordenadas = $extensionAttributes->getCoordenadas();
        $tiempo = $extensionAttributes->getTiempo();

        $billingAddress = $addressInformation->getBillingAddress();
        $billingAddress->setCoordenadas($coordenadas);
        $billingAddress->setTiempo($tiempo);


        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingAddress->setCoordenadas($coordenadas);
        $shippingAddress->setTiempo($tiempo);

        /*
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setDeliveryDate($deliveryDate);
        */
    }
}