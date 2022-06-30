<?php
namespace VexShipping\Glovo\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
   
    protected $_quote;

    protected $_helper;

    private $_cityOptions;

    public function __construct(
        \Magento\Checkout\Model\Session $quote
    )
    {
        $this->_quote                        = $quote;
    }

    public function process($jsLayout)
    {      
        $quote = $this->_quote->getQuote();
        $customAttributeCodecoordenadas = 'coordenadas';

        $customFieldCoordenadas = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCodecoordenadas,
            'label' => 'Coordenadas',
            'provider' => 'checkoutProvider',
            'sortOrder' => 41,
            'validation' => [],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => false,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCodecoordenadas] = $customFieldCoordenadas;



        $customAttributetiempo = 'tiempo';

        $customFieldTiempo = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributetiempo,
            'label' => 'Tiempo',
            'provider' => 'checkoutProvider',
            'sortOrder' => 42,
            'validation' => [],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => false,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributetiempo] = $customFieldTiempo;



        return $jsLayout;
    }
    
   
}