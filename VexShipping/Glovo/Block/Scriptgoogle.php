<?PHP 

namespace VexShipping\Glovo\Block;

class Scriptgoogle extends \Magento\Framework\View\Element\Template
{

	protected $_commentFactory;
    protected $_coreRegistry = null;

	public function __construct(
        \VexShipping\Glovo\Model\BrandFactory $brandFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->_commentFactory = $brandFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }


   	public function datos(){

        $id = $this->getRequest()->getParam('id');
        $rowData = $this->_commentFactory->create()->getCollection();
        $re = false;
        $datos = array();

        if ($id) {
            $rowData->addFieldToFilter('id', ['eq'=>$id]);

           if (count($rowData)) {
               $re=true;
               $datos = $rowData->getData();
           }
        }

        return array('status'=>$re, 'datos'=>$datos);
    }

    public function getApiKey()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $key = $objectManager->get('VexShipping\Glovo\Helper\Data')->getGoogleKey();
        return $key;
    }
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}