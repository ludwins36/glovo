<?php
namespace VexShipping\Glovo\Model\Config\Source;

class Lista implements \Magento\Framework\Option\ArrayInterface
{
 public function toOptionArray()
 {
  return [
  	['value' => '0', 'label' => __('Free')],
    ['value' => '1', 'label' => __('API Glovo calculated cost')],
    ['value' => '2', 'label' => __('Based on a fixed price')]
  ];
 }
}