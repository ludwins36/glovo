<?php
/**
 * Copyright © 2015 Vexsoluciones. All rights reserved.
 */

namespace VexShipping\Glovo\Model\ResourceModel;

class Glovo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vexsoluciones_glovo', 'id');
    }
}
