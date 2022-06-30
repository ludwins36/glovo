<?php
/**
 * Blog
 * 
 * @author Slava Yurthev
 */
namespace VexShipping\Glovo\Controller\Adminhtml\Glovo;

use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends Action {
	protected $_resultPageFactory;
	protected $_resultPage;
	public function __construct(Context $context, PageFactory $resultPageFactory){
		parent::__construct($context);
		$this->_resultPageFactory = $resultPageFactory;
	}
	public function execute(){
		$this->_setPageData();
		return $this->getResultPage();
	}
	protected function _isAllowed(){
		return $this->_authorization->isAllowed('VexShipping_Glovo::glovo');
	}
	public function getResultPage(){
		if (is_null($this->_resultPage)){
			$this->_resultPage = $this->_resultPageFactory->create();
		}
		return $this->_resultPage;
	}
	protected function _setPageData(){
		$resultPage = $this->getResultPage();
		$resultPage->setActiveMenu('VexShipping_Glovo::glovo');
		$resultPage->getConfig()->getTitle()->prepend((__('Glovo')));
		$resultPage->addBreadcrumb(__('Glovo'), __('List'));
		return $this;
	}
}