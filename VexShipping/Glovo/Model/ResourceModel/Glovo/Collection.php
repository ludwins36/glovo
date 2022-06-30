<?php
/**
 * Copyright © 2015 Vexsoluciones. All rights reserved.
 */

namespace VexShipping\Glovo\Model\ResourceModel\Glovo;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VexShipping\Glovo\Model\Glovo', 'VexShipping\Glovo\Model\ResourceModel\Glovo');
    }
}
