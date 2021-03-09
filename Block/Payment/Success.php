<?php
namespace Pagofacil\Pagofacildirect\Block\Payment;

class Success extends \Magento\Framework\View\Element\Template
{
        protected $_coreRegistry;
            
	public function __construct(
                \Magento\Framework\View\Element\Template\Context $context,
                \Magento\Framework\Registry $coreRegistry
                )
	{
		parent::__construct($context);
                $this->_coreRegistry = $coreRegistry;
	}
        
        public function getSuccessMessage() {
            
            echo 'Success - getSuccessMessage: <br>';
            return $this->_coreRegistry->registry('template_data');
        }
        
}
