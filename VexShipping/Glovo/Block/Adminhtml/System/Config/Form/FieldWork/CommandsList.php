<?php
namespace  VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork;

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
        $dias[1] = __("Monday");
        $dias[2] = __("Tuesday");
        $dias[3] = __("Wednesday");
        $dias[4] = __("Thursday");
        $dias[5] = __("Friday");
        $dias[6] = __("Saturday");
        $dias[0] = __("Sunday");




        return $dias;
    }
}