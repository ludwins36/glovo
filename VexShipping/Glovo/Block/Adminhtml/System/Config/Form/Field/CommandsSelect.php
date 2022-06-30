<?php
/**
 * Magento Chatbot Integration
 * Copyright (C) 2018
 *
 * This file is part of Werules/Chatbot.
 *
 * Werules/Chatbot is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * MIT License for more details.
 *
 * You should have received a copy of the MIT License
 * along with this program. If not, see <https://opensource.org/licenses/MIT>.
 */

namespace  VexShipping\Glovo\Block\Adminhtml\System\Config\Form\Field;

class CommandsSelect extends \Magento\Framework\View\Element\Html\Select
{
    
    protected $_commandsList;

    public function __construct(
        \VexShipping\Glovo\Block\Adminhtml\System\Config\Form\Field\CommandsList $commandsList,
        \Magento\Backend\Block\Template\Context $context, array $data = array())
    {
        parent::__construct($context, $data);
        $this->_commandsList = $commandsList;
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_commandsList->toOptionArray() as $option) {
                $this->addOption($option['value'], $option['label']);
            }
        }
        return parent::_toHtml();
    }

    public function getName()
    {
        return $this->getInputName();
    }
}