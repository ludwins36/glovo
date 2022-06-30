<?PHP

namespace VexShipping\Glovo\Plugin\Block\Adminhtml;

use Magento\Framework\Exception\LocalizedException;     


class Detalle
{
  
    public function afterToHtml(\Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $result) {
 
        
        $order = $subject->getOrder();
         
        $blockShippingDate = $subject->getLayout()->createBlock(
            'VexShipping\Glovo\Block\Adminhtml\Order\Detalle'
        );
 
        $blockShippingDate->setTemplate('VexShipping_Glovo::order/Detalle.phtml'); // FeFacturacionFieldsView
    
        if ($blockShippingDate !== false) {
  
            $result = $result.$blockShippingDate->toHtml();
        }
 
        return $result;
    }
}