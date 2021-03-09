<?php
namespace Pagofacil\Pagofacildirect\Block\Payment;

class Error extends \Magento\Framework\View\Element\Template
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

        public function getErrorMessage() {
            
            echo 'Error - getErrorMessage: <br>';
            return $this->_coreRegistry->registry('template_data');
        }
}
