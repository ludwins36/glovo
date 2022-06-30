<?php
namespace  VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork;

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
        $meses[0] = __("00:00");
        $meses[1] = __("01:00"); 
        $meses[2] = __("02:00"); 
        $meses[3] = __("03:00"); 
        $meses[4] = __("04:00"); 
        $meses[5] = __("05:00"); 
        $meses[6] = __("06:00"); 
        $meses[7] = __("07:00"); 
        $meses[8] = __("08:00"); 
        $meses[9] = __("09:00"); 
        $meses[10] = __("10:00"); 
        $meses[11] = __("11:00");
        $meses[12] = __("12:00"); 
        $meses[13] = __("13:00"); 
        $meses[14] = __("14:00"); 
        $meses[15] = __("15:00"); 
        $meses[16] = __("16:00"); 
        $meses[17] = __("17:00"); 
        $meses[18] = __("18:00"); 
        $meses[19] = __("19:00"); 
        $meses[20] = __("20:00"); 
        $meses[21] = __("21:00"); 
        $meses[22] = __("22:00");
        $meses[23] = __("23:00"); 

        return $meses;
    }
}