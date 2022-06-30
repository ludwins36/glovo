<?php
namespace VexShipping\Glovo\Model;

use Magento\Framework\Data\OptionSourceInterface;


class GlovoStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
                        [
                            'label' => __('Not sent'),
                            'value' => 1
                        ],
                        [
                            'label' => __('Sent'),
                            'value' => 2
                        ],
                        [
                            'label' => __('Cancelled'),
                            'value' => 0
                        ]
                    ];
        return $options;
    }
}
