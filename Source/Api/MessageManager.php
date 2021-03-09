<?php

namespace PagoFacil\Pagofacildirect\Source\Api;

use PagoFacil\Pagofacildirect\Source\Api\MessageManagerAbstract;

class MessageManager extends MessageManagerAbstract{

    public function __construct() {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('MessageManager - __construct 000: ');
        
    }
    
    public function ErrorMsg($text) {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('MessageManager - ErrorMsg 000: ');
        $logger->info('MessageManager - ErrorMsg $text: ');
        $logger->info(print_r($text, true));
        
        parent::ErrorMsg($text);
        //return $this;
    }
}