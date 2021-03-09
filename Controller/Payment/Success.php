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
        //echo 'Controlador Success execute $post_data: <br>';
        //var_dump($post_data);
        
        if(empty($post_data)){
            header('Location: '.$this->url());
        } else {
            
            $quote_id = base64_decode($this->getRequest()->getParam('id'));
            //echo 'Controlador GetMsiOptions $_GET - $quote_id: '.$quote_id.'<br>';
            
            $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $cipherKey =  $scopeConfig->getValue('payment/pagofacil_pagofacildirect/display_user_phase_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //echo'Controlador Success 3ds - $cipherKey: '.$cipherKey.'<br>';
            
            $descifrar =  new PagoFacilDescifrar();
            $dataResponse = $descifrar->desencriptar_php72($post_data['response'], $cipherKey);
            $dataResponse = json_decode($dataResponse);
            echo'Controlador Success 3ds - json_decode $dataResponse: <br>';
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
                //var_dump($dataResponse);
                //echo 'arrayResponse - SI Amex - $dataResponse - $dataResponse->autorizado: '.$dataResponse->autorizado.'<br>';
                //echo 'arrayResponse - SI Amex - $dataResponse - $dataResponse->texto: '.$dataResponse->texto.'<br>';
                //Die('Die en SI SI Amex');
            } 
            
            //echo 'arrayResponse - SI VISA-MC: <br>';
            //echo 'arrayResponse - SI VISA-MC - $dataResponse: <br>';
            //var_dump($dataResponse);
            
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
                    echo'Controlador Success 3ds - PagoFacilDescifrar $store_id: <br>';
                    //var_dump($store_id);
                    $website_id = (int)$store_manager->getWebsiteId();
                    echo'Controlador Success 3ds - PagoFacilDescifrar $website_id: <br>';
                    //var_dump($website_id);

                    echo'Controlador Success 3ds - PagoFacilDescifrar $dataResponse->texto: '.$dataResponse->texto.'<br>';

                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory 000: <br>';
                    $quoteFactory = $this->_objectManager->create('Magento\Quote\Model\QuoteFactory');
                    $quote = $quoteFactory->create()->load($quote_id);

                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory - getId: '.$quote->getId().'<br>';
                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory - getEntityId: '.$quote->getEntityId().'<br>';
                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory - data: <br>';
                    ////////// var_dump($quote->getData());
                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory - getItemsCollection: <br>';
                    //var_dump($quote->getItemsCollection());

                    echo'Controlador Success 3ds - PagoFacilDescifrar $quoteFactory - getAllVisibleItems: <br>';
                    //var_dump($quote->getAllVisibleItems());

                    foreach($quote->getAllVisibleItems() as $item) {
                        echo 'ID: '.$item->getProductId().'<br />';
                        echo 'Name: '.$item->getName().'<br />';
                        echo 'Sku: '.$item->getSku().'<br />';
                        echo 'Quantity: '.$item->getQty().'<br />';
                        echo 'Price: '.$item->getPrice().'<br />';
                        echo "<br />";            
                    }

                    /////// die('Die en getAllVisibleItems: ');

                    echo'Controlador Success 3ds - PagoFacilDescifrar CustomerSession isLoggedIn: <br>';
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerSession = $objectManager->get('Magento\Customer\Model\Session');

                    if($customerSession->isLoggedIn()) {

                        echo'El cliente SI esta Logeado: <br>';
                        echo'Cliente SI Logeado Customer Id: '.$customerSession->getCustomer()->getId().'<br>';
                        echo'Cliente SI Logeado Customer Email: '.$customerSession->getCustomer()->getEmail().'<br>';

                        $customerRepository = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
                        $customer = $customerRepository->getById($customerSession->getCustomer()->getId());
                        echo'Cliente SI Logeado $customer: <br>';
                        ////////// var_dump($customer);

                        $quote->setCurrency();
                        echo'Cliente SI Logeado setCurrency: <br>';
                        $quote->assignCustomer($customer);
                        echo'Cliente SI Logeado quote->assignCustomer: <br>';

                        $customerAddress = $this->_objectManager->create('Magento\Customer\Model\Address');
                        $shipping_id =  (int)$customer->getDefaultShipping();
                        echo'Cliente SI Logeado $shipping_id: <br>';
                        ////////// var_dump($shipping_id);
                        $address = $customerAddress->load($shipping_id);

                        echo'Cliente SI Logeado firstname: '.$customer->getFirstName().'<br>';
                        echo'Cliente SI Logeado lastname: '.$customer->getLastName().'<br>';
                        echo'Cliente SI Logeado lastname: '.$customer->getEmail().'<br>';
                        echo'Cliente SI Logeado street: '.$address->getData('street').'<br>';
                        echo'Cliente SI Logeado city: '.$address->getData('city').'<br>';
                        echo'Cliente SI Logeado country_id: '.$address->getData('country_id').'<br>';
                        echo'Cliente SI Logeado region: '.$address->getData('region').'<br>';
                        echo'Cliente SI Logeado postcode: '.$address->getData('postcode').'<br>';
                        echo'Cliente SI Logeado telephone: '.$address->getData('telephone').'<br>';
                        echo'Cliente SI Logeado fax: '.$address->getData('fax').'<br>';

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

                        echo'Cliente SI Logeado $address_data Array: <br>';
                        ////////// var_dump($address_data);

                        $quote->getBillingAddress()->addData($address_data['shipping_address']);
                        $quote->getShippingAddress()->addData($address_data['shipping_address']);

                        echo'Cliente SI Logeado getShippingAddress->addData: <br>';

                        $quote->setPaymentMethod('PagoFacil-Payment');
                        echo'Cliente SI Logeado setPaymentMethod: <br>';
                        $quote->setPaymentMethod('PagoFacil-3ds');
                        echo'Cliente SI Logeado setPaymentMethod: <br>';
                        $quote->setInventoryProcessed(false);
                        echo'Cliente SI Logeado setInventoryProcessed: <br>';
                        $quote->save(); //Now Save quote and your quote is ready
                        echo'Cliente SI Logeado quote->save: <br>';
                        $quote->getPayment()->importData(['method' => 'checkmo']);
                        echo'Cliente SI Logeado getPayment->importData: <br>';

                        // Collect Totals & Save Quote
                        $quote->collectTotals()->save();

                        //\Magento\Quote\Model\QuoteManagement
                        $quoteManagement = $this->_objectManager->create('Magento\Quote\Model\QuoteManagement');
                        echo'Cliente SI Logeado quoteManagement-create: <br>';
                        $order = $quoteManagement->submit($quote);
                        echo'Cliente SI Logeado quoteManagement->submit: <br>';

                        $order->setEmailSent(0);
                        $increment_id = $order->getRealOrderId();
                        echo'Cliente SI Logeado increment_id getRealOrderId(): <br>';
                        var_dump($increment_id);
                        $order_id = $order->getId();
                        echo'Cliente SI Logeado $order_id: <br>';
                        var_dump($order_id);
                        
                        if(!empty($order_id)){
                            
                            echo'Cliente SI Logeado Crear Invoice: <br>';
                            $orderRepository = $this->_objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
                            $order = $orderRepository->get((int)$order_id);
                            if ($order->canInvoice()) {
                                echo'Cliente SI Logeado Crear Invoice - IF canInvoice: <br>';
                                $invoiceService = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService');
                                $invoice = $invoiceService->prepareInvoice($order);
                                $invoice->register();
                                $invoice->save();
                                echo'Cliente SI Logeado Crear Invoice - $invoice->save: <br>';

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

                                echo'Cliente SI Logeado Crear Invoice - addStatusHistoryComment: <br>';
                                
                                $order_message = 'Se ha creado con éxito el pedido número: '.$order->getRealOrderId();
                                echo'Cliente SI Logeado Crear Invoice - $order_message: '.$order_message.'<br>';
                                $template_data = array('order_message' => $order_message);
                                
                                $this->coreRegistry->register('template_data', $template_data);
                                echo'Cliente SI Logeado Crear Invoice - coreRegistry->register: <br>';
                                $resultPage = $this->resultPageFactory->create();
                                echo'Cliente SI Logeado Crear Invoice - $this->resultPageFactory: <br>';
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
    
}