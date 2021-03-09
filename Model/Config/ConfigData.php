<?php

declare(strict_types=1);

namespace Pagofacil\Pagofacildirect\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Pagofacil\Pagofacildirect\Model\Payment;

class ConfigData
{
    
    /** 
     * @var Pagofacil\Pagofacildirect\Model\Payment::CODE
     */
    private $module_code;
    
    /** 
     * @var Pagofacil\Pagofacildirect\Model\Payment
     */
    private $paymentModel;
    
    /** 
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    public function __construct(Payment $paymentModel, ScopeConfigInterface $scopeConfig){
        
        $this->paymentModel = $paymentModel;
        $this->scopeConfig = $scopeConfig;
        $this->module_code = Payment::CODE;
    }
    /**
     * @param string $field
     * @param string $code
     * @return mixed
     */
    public function getConfigDataPagofacil(string $field)
    {
        $path = "payment/{$this->module_code}/$field";
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null);
    }
}