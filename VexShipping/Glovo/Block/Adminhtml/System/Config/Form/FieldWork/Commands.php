<?php
namespace VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork;

class Commands extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;
    protected $_itemRendererCommands;
    protected $_itemRendererCommandsEnvio;
    protected $_itemRendererCommandsEnvioFin;
    protected $_itemRendererYesNo;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = array()
    )
    {
        $this->_elementFactory  = $elementFactory;
        parent::__construct($context,$data);
    }
    protected function _construct()
    {
        
        $this->addColumn('dia', array(
            'label' => __("Day"),
            'column_css_class' => 'width125',
            'renderer' => $this->_getRendererCommands()
        ));
        $this->addColumn('hora', array(
            'label' => __("Start Time"),
            'column_css_class' => 'width125',
            'renderer' => $this->_getRendererCommands2()
        ));
        $this->addColumn('hora_fin', array(
            'label' => __("End Time"),
            'column_css_class' => 'width125',
            'renderer' => $this->_getRendererCommands3()
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = __("Add");
        parent::_construct();
    }

//    protected function _prepareToRender() {}

    

    protected function _getRendererCommands()
    {
        if (!$this->_itemRendererCommands)
        {
            $this->_itemRendererCommands = $this->getLayout()->createBlock(
                'VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork\CommandsSelect',
                '',
                array('data' => array('is_render_to_js_template' => true))
//                array('is_render_to_js_template' => true)
            )->setExtraParams('style="width: 110px;padding:3px;"');
        }
        return $this->_itemRendererCommands;
    }

    protected function _getRendererCommands2()
    {
        if (!$this->_itemRendererCommandsEnvio)
        {
            $this->_itemRendererCommandsEnvio = $this->getLayout()->createBlock(
                'VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork\CommandsSelectEnvio',
                '',
                array('data' => array('is_render_to_js_template' => true))
            )->setExtraParams('style="width: 130px;padding:3px;"');
        }
        return $this->_itemRendererCommandsEnvio;
    }


    protected function _getRendererCommands3()
    {
        if (!$this->_itemRendererCommandsEnvioFin)
        {
            $this->_itemRendererCommandsEnvioFin = $this->getLayout()->createBlock(
                'VexShipping\Glovo\Block\Adminhtml\System\Config\Form\FieldWork\CommandsSelectEnvioFin',
                '',
                array('data' => array('is_render_to_js_template' => true))
            )->setExtraParams('style="width: 130px;padding:3px;"');
        }
        return $this->_itemRendererCommandsEnvioFin;
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = array();
        $optionExtraAttr['option_' . $this->_getRendererCommands()->calcOptionHash($row->getData('dia'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getRendererCommands2()->calcOptionHash($row->getData('hora'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getRendererCommands3()->calcOptionHash($row->getData('hora_fin'))] = 'selected="selected"';

        $row->setData(
            'option_extra_attrs', $optionExtraAttr
        );
    }
}