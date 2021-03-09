<?php

namespace Pagofacil\Pagofacildirect\Source\Logger;

class PagofacilLog {
    
    const CONSOLE_NONE = 0;
    const CONSOLE_TRACE = 2;
    const CONSOLE_DEBUG = 4;
    const CONSOLE_INFO = 8;
    const CONSOLE_WARNING = 16;
    const CONSOLE_ERROR = 32;
    const CONSOLE_CRITICAL = 64;

    private static $writer;
    
    private static $logger;
    
    private static $log_file = '/var/log/pagofacil.log';
    
    private static function _log($type, $flag, $text) {
        
        /*
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $loggerzend = new \Zend\Log\Logger();
        $loggerzend->addWriter($writer);
        $loggerzend->info('PagofacilLog _log 0000: ');
        $loggerzend->info('PagofacilLog _log Type: ');
        $loggerzend->info(print_r($type, true));
        $loggerzend->info('PagofacilLog _log Flag: ');
        $loggerzend->info(print_r($flag, true));
        $loggerzend->info('PagofacilLog _log Text: ');
        $loggerzend->info(print_r($text, true));
        */
        
        $check_flag = self::checkFlag($flag);
        
        if(self::checkFlag($flag)){
            self::record('[' . strtoupper($type) . ']', $text);
        }
        
    }
    
    private static function checkFlag($flag) {
        
        $approved = false;

        /*
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $loggerzend = new \Zend\Log\Logger();
        $loggerzend->addWriter($writer);
        $loggerzend->info('PagofacilLog checkFlag 0000: ');
        $loggerzend->info('PagofacilLog checkFlag Flag: ');
        $loggerzend->info(print_r($flag, true));
        $loggerzend->info('PagofacilLog checkFlag CONSOLE_NONE: ');
        $loggerzend->info(print_r(self::CONSOLE_NONE, true));
        */
        
        $arithmetic = (int)$flag + (int) self::CONSOLE_NONE;
        
        if($arithmetic == $flag){
            $approved = true;
        }
        
        return $approved;
    }
    
    private static function record($prefix, $text) {
        
        $output = $prefix . ': ' . print_r($text, true);

        /*
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $loggerzend = new \Zend\Log\Logger();
        $loggerzend->addWriter($writer);
        $loggerzend->info('PagofacilLog record 0000: ');
        $loggerzend->info('PagofacilLog record $prefix: ');
        $loggerzend->info(print_r($prefix, true));
        $loggerzend->info('PagofacilLog record $text: ');
        $loggerzend->info(print_r($text, true));
        $loggerzend->info('PagofacilLog record $this->writer 0001: ');
        $loggerzend->info('PagofacilLog record $this->log_file: ');
        $loggerzend->info(print_r(self::$log_file, true));
        */
        
        self::$writer = new \Zend\Log\Writer\Stream(BP . self::$log_file);
        self::$logger = new \Zend\Log\Logger();
        self::$logger->addWriter(self::$writer);
        self::$logger->info($output);
        
    }
    
    public static function info($text) {

        /*
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $loggerzend = new \Zend\Log\Logger();
        $loggerzend->addWriter($writer);
        $loggerzend->info('PagofacilLog info 0000: ');
        $loggerzend->info('PagofacilLog info Text: ');
        $loggerzend->info(print_r($text, true));
        */
        
        self::_log('info', self::CONSOLE_NONE, $text);
        
    }
    
    public static function error($text) {        
        self::_log('error', self::CONSOLE_NONE, $text);
    }
    
    public static function warn($text) {
        self::_log('warning', self::CONSOLE_NONE, $text);
    }

}