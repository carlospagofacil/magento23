<?php
namespace Pagofacil\Pagofacildirect\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\Order\Invoice;
use Pagofacil\Pagofacildirect\Model\Config\ConfigData;
use Pagofacil\Pagofacildirect\Model\Config\PagofacilConfigData;
use Pagofacil\Pagofacildirect\Source\Client\PagoFacilDescifrar;

class Success extends \Magento\Framework\App\Action\Action
{
    
    protected $pagofacilConfig;
    
    protected $resultPageFactory;
    
    protected $coreRegistry;

    public function __construct(
    Context $context,
    PageFactory $resultPageFactory,
    Registry $coreRegistry
) {
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->coreRegistry = $coreRegistry;
}

    public function execute(){

        $post_data = $this->getRequest()->getPost()->toArray();
        
        if(empty($post_data)){
            header('Location: '.$this->url());
        } else {
            
            $quote_id = base64_decode($this->getRequest()->getParam('id'));
            $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $cipherKey =  $scopeConfig->getValue('payment/pagofacil_pagofacildirect/display_user_phase_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            if($this->orderAlreadyExists($quote_id)){
                echo 'IF SI orderAlreadyExists - Redireccionar a: '.$this->url().'<br>';
                $mensaje = 'Ya existe una orden con este pedido';
                $template_data = array('message' => $mensaje);
                $this->coreRegistry->register('template_data', $template_data);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->addHandle('pagofacildirect_payment_error'); 
                return $resultPage;
            }
            
            $descifrar =  new PagoFacilDescifrar();
            $dataResponse = $descifrar->desencriptar_php72($post_data['response'], $cipherKey);
            $dataResponse = json_decode($dataResponse);
            //echo'Controlador Success 3ds - json_decode $dataResponse: <br>';
            //var_dump($dataResponse);
            
            $pageFactory = $this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
        
            if(empty($dataResponse->transaccion)){
                echo 'arrayResponse - transaccion SI esta Vacio <br>';
                echo 'arrayResponse - transaccion SI esta Vacio - texto: '.$dataResponse->texto.'<br>';
                
                $template_data = array('message' => $dataResponse->texto);
                $this->coreRegistry->register('template_data', $template_data);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->addHandle('pagofacildirect_payment_error'); 
                return $resultPage;

            } 
            
            if (!empty($dataResponse->u) && !empty($dataResponse->response)){
                echo 'arrayResponse - SI Amex: <br>';
                echo 'arrayResponse - SI Amex - $dataResponse: <br>';
                $dataResponse = $dataResponse->response;
            } 
            
            echo '$dataResponse->autorizado '.$dataResponse->autorizado.': <br>';
            echo '$dataResponse - $dataResponse->texto: '.$dataResponse->texto.'<br>';
            
            if($dataResponse->autorizado == 0){
                    echo 'dataResponse->autorizado == 0 - $dataResponse->texto: '.$dataResponse->texto.'<br>';
                    
                    $template_data = array('message' => $dataResponse->texto);

                    $this->coreRegistry->register('template_data', $template_data);
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->addHandle('pagofacildirect_payment_error');
                    return $resultPage;
            } else {

                    $address_data = ['shipping_address' =>[
                                                'firstname'    => '',
                                                'lastname'     => '',
                                                'street' => '',
                                                'city' => '',
                                                'country_id' => '',
                                                'region' => '',
                                                'postcode' => '',
                                                'telephone' => '',
                                                'fax' => '',
                                                'save_in_address_book' => 1
                                            ]
                                    ];

                    $store_manager = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore();
                    $store_id = (int)$store_manager->getId();
                    $website_id = (int)$store_manager->getWebsiteId();

                    $quoteFactory = $this->_objectManager->create('Magento\Quote\Model\QuoteFactory');
                    $quote = $quoteFactory->create()->load($quote_id);

                    $product_list = array();
                    foreach($quote->getAllVisibleItems() as $item) {
                        $product_list[] = array(
                                        'product_id' => $item->getProductId(),
                                        'product_name' => $item->getName(),
                                        'product_sku' => $item->getSku(),
                                        'product_qty' => $item->getQty(),
                                        'product_price' => $item->getPrice()
                                );
                    }

                    echo'Controlador Success 3ds - PagoFacilDescifrar CustomerSession isLoggedIn: <br>';
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerSession = $objectManager->get('Magento\Customer\Model\Session');

                    if($customerSession->isLoggedIn()) {

                        $customerRepository = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
                        $customer = $customerRepository->getById($customerSession->getCustomer()->getId());

                        $quote->setCurrency();
                        $quote->assignCustomer($customer);

                        $customerAddress = $this->_objectManager->create('Magento\Customer\Model\Address');
                        $shipping_id =  (int)$customer->getDefaultShipping();

                        $address = $customerAddress->load($shipping_id);
                        $address_data['shipping_address']['firstname'] = $customer->getFirstName();
                        $address_data['shipping_address']['lastname'] = $customer->getLastName();
                        $address_data['shipping_address']['street'] = $address->getData('street');
                        $address_data['shipping_address']['city'] = $address->getData('city');
                        $address_data['shipping_address']['country_id'] = $address->getData('country_id');
                        $address_data['shipping_address']['region'] = $address->getData('region');
                        $address_data['shipping_address']['postcode'] = $address->getData('postcode');
                        $address_data['shipping_address']['telephone'] = $address->getData('telephone');
                        $address_data['shipping_address']['fax'] = $address->getData('fax');
                        $address_data['shipping_address']['save_in_address_book'] = 0;
                        
                        $customer_name = $customer->getFirstName() . ' ' . $customer->getLastName();

                        $quote->getBillingAddress()->addData($address_data['shipping_address']);
                        $quote->getShippingAddress()->addData($address_data['shipping_address']);

                        $quote->setPaymentMethod('PagoFacil-Payment');
                        $quote->setPaymentMethod('PagoFacil-3ds');
                        $quote->setInventoryProcessed(false);
                        $quote->save();
                        $quote->getPayment()->importData(['method' => 'checkmo']);

                        // Collect Totals & Save Quote
                        $quote->collectTotals()->save();

                        $quoteManagement = $this->_objectManager->create('Magento\Quote\Model\QuoteManagement');
                        $order = $quoteManagement->submit($quote);

                        $order->setEmailSent(0);
                        $increment_id = $order->getRealOrderId();
                        $order_id = $order->getId();
                        
                        if(!empty($order_id)){
                            
                            $orderRepository = $this->_objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
                            $order = $orderRepository->get((int)$order_id);
                            if ($order->canInvoice()) {
                                
                                $invoiceService = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService');
                                $invoice = $invoiceService->prepareInvoice($order);
                                $invoice->register();
                                $invoice->save();

                                $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction');
                                $transactionSave = $transaction->addObject(
                                    $invoice
                                )->addObject(
                                    $invoice->getOrder()
                                );

                                $transactionSave->save();
                                $order->addStatusHistoryComment(
                                    __('PagoFacil Notifica que su pago ha creado la factura #%1.', $invoice->getId())
                                )
                                    ->setIsCustomerNotified(true)
                                    ->save();

                                $order_message = 'Se ha creado con éxito el pedido número: '.$order->getRealOrderId();
                                $template_data = array(
                                                'order_message' => $order_message,
                                                'customer_name' => $customer_name,
                                                'product_list' => $product_list
                                        );
                                
                                $this->coreRegistry->register('template_data', $template_data);
                                $resultPage = $this->resultPageFactory->create();
                                $resultPage->addHandle('pagofacildirect_payment_success');
                                echo'Cliente SI Logeado Crear Invoice - resultPage->addHandle: <br>';
                                return $resultPage;
                
                            }
                        }
                        
                    }

            }
            
        }
        
    } /* Fin de execute */
    
    protected function url() {

        return sprintf(
          "%s://%s",
          isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
          $_SERVER['SERVER_NAME']
        );
    }
    
    private function orderAlreadyExists($quote_id) {
        
        $order_exists = false;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sales_order = $resource->getTableName('sales_order');
        $sql = "SELECT entity_id, state, status, quote_id, increment_id FROM `".$sales_order."` WHERE quote_id = '".$quote_id."'";
        $result = $connection->fetchAll($sql);

        if(count($result) > 0){
            $order_exists = true;
        }
        
        return $order_exists;
    }
    
}