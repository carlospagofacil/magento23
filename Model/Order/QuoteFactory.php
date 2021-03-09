<?php

namespace Pagofacil\Pagofacildirect\Model\Order;

class QuoteFactory
{
    protected $quoteFactory;
    
    public function __construct( \Magento\Quote\Model\QuoteFactory $quoteFactory ){

       $this->quoteFactory = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
    }
    
    public function quote($quote_id) {
        $this->quoteFactory->create()->load($quote_id);
    }

}