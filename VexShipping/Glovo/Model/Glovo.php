<?php
/**
 * Copyright © 2015 Vexsoluciones. All rights reserved.
 */

namespace VexShipping\Glovo\Model;

class Glovo extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('VexShipping\Glovo\Model\ResourceModel\Glovo');
    }
}
