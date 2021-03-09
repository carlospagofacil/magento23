<?php

namespace Pagofacil\Pagofacildirect\Model;

use \Pagofacil\Pagofacildirect\Source\Api\TransactionApi;
use PagoFacil\Pagofacildirect\Exceptions\PaymentException;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'pagofacil_pagofacildirect';

    protected $_code = self::CODE;

    protected $_canAuthorize = true;
    protected $_canCapture = true;
    
    protected $pagofacilconfig;
    
    protected $payment_data;
    
    protected $transaction_api;
    
    protected $_logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Pagofacil\Pagofacildirect\Model\Config\PagofacilConfigData $pagofacilconfigdata,
        array $data = array()
            ) 
    {
        parent::__construct(
            $context, $registry, $extensionFactory, $customAttributeFactory,
            $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, null,
            null, $data
        );
        
        $this->pagofacilconfig = $pagofacilconfigdata;
        $this->pagofacilconfig->setUserId($this->getConfigData('display_user_id'));
        $this->pagofacilconfig->setBranchOfficeId($this->getConfigData('display_user_branch_office_id'));
        $this->pagofacilconfig->setPhaseId($this->getConfigData('display_user_phase_id'));
        $this->pagofacilconfig->setEndpointSandbox($this->getConfigData('endpoint_sandbox'));
        $this->pagofacilconfig->setEndpointProduction($this->getConfigData('endpoint_production'));
        $this->pagofacilconfig->setIsSandbox($this->getConfigData('is_sandbox'));
        $this->pagofacilconfig->setMonthyInstallmentEnabled($this->getConfigData('monthy_installment_enabled'));
        $this->pagofacilconfig->setCctypes($this->getConfigData('cctypes'));
        //echo 'class Payment Construct - getVerificationEndPointUrl: '.$this->pagofacilconfig->getVerificationEndPointUrl().'<br>';
        
        $this->transaction_api = new TransactionApi();
        
        $this->_logger = $logger;
    }

    public function assignData(\Magento\Framework\DataObject $data) {
        
        parent::assignData($data);
        
        $infoInstance = $this->getInfoInstance();
        $additionalData = ($data->getData('additional_data') != null) ? $data->getData('additional_data') : $data->getData();
        
        $infoInstance->setAdditionalInformation('cc_number',
            isset($additionalData['cc_number']) ? $additionalData['cc_number'] :  null
        );
        $infoInstance->setAdditionalInformation('cc_cid',
            isset($additionalData['cc_cid']) ? $additionalData['cc_cid'] : null
        );
        $infoInstance->setAdditionalInformation('cc_exp_month',
            isset($additionalData['cc_exp_month']) ? $additionalData['cc_exp_month'] : null
        );
        $infoInstance->setAdditionalInformation('cc_exp_year',
            isset($additionalData['cc_exp_year']) ? $additionalData['cc_exp_year'] : null
        );
        $infoInstance->setAdditionalInformation('monthly_installments',
            isset($additionalData['monthly_installments']) ? $additionalData['monthly_installments'] : null
        );
        $infoInstance->setAdditionalInformation('openpay_cc',
            isset($additionalData['openpay_cc']) ? $additionalData['openpay_cc'] : null
        );
        $infoInstance->setAdditionalInformation('cc_cid',
            isset($additionalData['cc_cid']) ? $additionalData['cc_cid'] : null
        );
        $infoInstance->setAdditionalInformation('installments',
            isset($additionalData['installments']) ? $additionalData['installments'] : null
        );
        return $this;
        
    }
    /**
     * Capture Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }
        
        $order = $payment->getOrder();
        $billingAddress = $order->getBillingAddress();
        $additional_data = (object) $this->getInfoInstance()->getAdditionalInformation();
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Capture 000: ');
        
        try {
            
            $this->transaction_api->createTransactionInformation($order, $billingAddress, $this->pagofacilconfig, $additional_data);
            
            //check if payment has been authorized
            if(is_null($payment->getParentTransactionId())) {
                $this->authorize($payment, $amount);
            }

            //build array of payment data for API request.
            $request = [
                'capture_amount' => $amount,
                //any other fields, api key, etc.
            ];
            
            //make API request to credit card processor.
            $response = $this->makeCaptureRequest($request);
            
            //transaction is done.
            $payment->setIsTransactionClosed(1);
            

        } catch (\Exception $e) {
            $this->debug($payment->getData(), $e->getMessage());
        }

        return $this;
    }

    /**
     * Authorize a payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        try {
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Authorize 0000: ');

            ///build array of payment data for API request.
            $request = [
                'cc_type' => $payment->getCcType(),
                'cc_exp_month' => $payment->getCcExpMonth(),
                'cc_exp_year' => $payment->getCcExpYear(),
                'cc_number' => $payment->getCcNumberEnc(),
                'amount' => $amount
            ];

            //check if payment has been authorized
            $response = $this->makeAuthRequest($request);
            
            $order = $payment->getOrder();

            try{
                /* Inicia la transacción con PagoFácil */
                $this->transaction_api->makeTransaction($this->pagofacilconfig);
                
                $logger->info('TRY $this->transaction_api - getId: ');
                $logger->info(print_r($this->transaction_api->charge()->getId(), true));
                
                $logger->info('TRY $this->transaction_api - getOrderId: ');
                $logger->info(print_r($this->transaction_api->charge()->getOrderId(), true));
                
                $logger->info('TRY $this->transaction_api - getMessage: ');
                $logger->info(print_r($this->transaction_api->charge()->getMessage(), true));
                
                if($this->transaction_api->charge()->getId() == 0 
                    && $this->transaction_api->charge()->getOrderId() == 0 
                    && $this->transaction_api->charge()->getMessage() == ''){
                    
                    $logger->info('TRY La Transacción es rechazada: ');
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error - La Transacción ha sido rechazada.'));
                    
                }
                
            }catch (Exception $exception) {
                
                throw new CouldNotSaveException(
                    __('An asd wrt rtyury. Please sdfg zxcvzcx wqerqwen.'.$exception->getMessage()),
                    $exception
                );
                
                $this->_logger->error($exception->getMessage());
                throw $exception->getMessage();
            }
            
            $order->addStatusHistoryComment("Pago recibido exitosamente")->setIsCustomerNotified(true);
            $order->setExtOrderId($this->transaction_api->charge()->getId());
            $order->save();

            $payment->setTransactionId($this->transaction_api->charge()->getId());
            $payment->setCcLast4($this->transaction_api->charge()->getCcdigits());
            $payment->setCcType($this->transaction_api->charge()->getCctype());
            $payment->setAdditionalInformation('PagoFacil direct payment', $this->transaction_api->charge()->getId());
            $payment->setIsTransactionPending(false);
            
            $logger->info('Complementar el Pedido Final: ');            
            $logger->info('Authorize setIsTransactionClosed: ');

        } catch (\Exception $e) {
            $this->debug($payment->getData(), $e->getMessage());
        }

        //processing is not done yet.
        $payment->setIsTransactionClosed(0);

        $logger->info('Authorize return $this: ');
        return $this;
    }
    
    /**
     * Set the payment action to authorize_and_capture
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return self::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Test method to handle an API call for authorization request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeAuthRequest($request)
    {
        $response = ['transactionId' => 123]; //todo implement API call for auth request.

        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed auth request.'));
        }

        return $response;
    }

    /**
     * Test method to handle an API call for capture request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeCaptureRequest($request)
    {
        $response = ['success']; //todo implement API call for capture request.

        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed capture request.'));
        }

        return $response;
    }
}