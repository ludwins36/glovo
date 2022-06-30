<?php
namespace  VexShipping\Glovo\Block\Adminhtml\System\Config\Form\Field;

class CommandsList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $commands = $this->toArray();
        $result = array();
        foreach ($commands as $key => $command)
        {
            $arr = array(
                'value' => $key,
                'label' => $command
            );
            array_push($result, $arr);
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {

        $dias = array();
        for ($i=1; $i <= 31; $i++) { 
            $dias[$i] = $i; 
        }


        return $dias;
    }
}