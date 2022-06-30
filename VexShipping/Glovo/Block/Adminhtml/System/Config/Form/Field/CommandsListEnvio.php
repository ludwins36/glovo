<?php
namespace  VexShipping\Glovo\Block\Adminhtml\System\Config\Form\Field;

class CommandsListEnvio implements \Magento\Framework\Option\ArrayInterface
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
        $meses = array();
        $meses[1] = __("January");
        $meses[2] = __("February"); 
        $meses[3] = __("March"); 
        $meses[4] = __("April"); 
        $meses[5] = __("May"); 
        $meses[6] = __("June"); 
        $meses[7] = __("July"); 
        $meses[8] = __("August"); 
        $meses[9] = __("September"); 
        $meses[10] = __("October"); 
        $meses[11] = __("November"); 
        $meses[12] = __("December"); 

        return $meses;
    }
}