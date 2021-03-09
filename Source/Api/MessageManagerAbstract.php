<?php

namespace PagoFacil\Pagofacildirect\Source\Api;

class MessageManagerAbstract {
    
    protected $messageManager;
    
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }
    
    public function ErrorMsg($text) {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('MessageManagerAbstract - ErrorMsg 000: ');
        $logger->info('MessageManagerAbstract - ErrorMsg $text: ');
        $logger->info(print_r($text, true));
        
        $this->messageManager->addWarningMessage($text);
        //$aaa = $this->messageManager;
        
        $logger->info('MessageManagerAbstract - ErrorMsg $aaa: ');
    }
    
}