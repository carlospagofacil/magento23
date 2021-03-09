<?php

namespace Pagofacil\Pagofacildirect\Source\Api;

use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\HTTP\Client\Curl;

class ConnectorApi {
    
    private $magento_curl;
        
    public function _callApi($url, $body) {
        
        $api_response = array();
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        $this->magento_curl = new Curl();
        $this->magento_curl->post($url, $body);
        $api_response['body'] = $this->magento_curl->getBody();
        $api_response['status'] = $this->magento_curl->getStatus();
        $api_response['headers'] = $this->magento_curl->getHeaders();
        
        return $api_response;
    }
    
    public function sendRequest($url, $body) {
        
        return $this->_callApi($url, $body);
    }
    
}