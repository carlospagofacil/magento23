<?php

namespace Pagofacil\Pagofacildirect\Source\Api;

use \Pagofacil\Pagofacildirect\Source\Api\ConnectorApi;
use \Pagofacil\Pagofacildirect\Source\Api\ResponseApi;
use \PagoFacil\Pagofacildirect\Exceptions\ApiclientException;

class TransactionApi {
 
    private $pagofacilconfig;
    
    private $transaccion_data;
    
    private $body;
    
    private $connector;
    
    private $response_api;

    private $charge;
    
    protected $messageManager;
    
    private function setBody() {
        $this->body = $this->transaccion_data;
    }
    
    public function makeTransaction($pagofacilconfig) {
        
        $this->pagofacilconfig = $pagofacilconfig;
        
        $transaction_body = $this->getBody();
        $transaction_url = $this->getUrl();
        
        $this->request();
    }
    
    public function getBody() {
        
        return urldecode(http_build_query($this->transaccion_data));
    }
    
    protected function getUrl() {

        /* Transaction url - $pagofacilconfig::TRANSACTION_METHOD */
        return $this->pagofacilconfig->getTransactionEndPointUrl();
    }

    private function request() {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('request 0000: ');
        
        $this->connector = new ConnectorApi();
        $response = $this->connector->sendRequest($this->getUrl(), $this->getBody());

        $this->response_api = new ResponseApi($response['body'], $response['status']);
        $logger->info('request $this->response_api - getId: ');
        $logger->info(print_r($this->response_api->charge()->getId(), true));
        $logger->info('request $this->response_api - getOrderId: ');
        $logger->info(print_r($this->response_api->charge()->getOrderId(), true));
        $logger->info('request $this->response_api - getMessage: ');
        $logger->info(print_r($this->response_api->charge()->getMessage(), true));
        
        $this->loadCharge($this->response_api->charge());

    }
    
    private function loadCharge($charge) {
        $this->charge = $charge;
    }
    
    public function charge() {
        return $this->charge;
    }
    
    public function createTransactionInformation($order, $billingAddress, $pagofacilconfig, $additional_data)
    {
        
        $this->transaccion_data = [
            'method' => $pagofacilconfig::TRANSACTION_METHOD,
            'data' => [
                'idUsuario' => $pagofacilconfig->getUserId(),
                'idSucursal' => $pagofacilconfig->getBranchOfficeId(),
                'idPedido' => $order->getRealOrderId(),
                'idServicio' => 3,
                'Source' => 'Magento 2 API',
                'monto' => $order->getGrandTotal(),
                'plan' => 'plan',
                'mensualidades' => $additional_data->monthly_installments,
                'numeroTarjeta' => $additional_data->cc_number,
                'cvt' => $additional_data->cc_cid,
                'mesExpiracion' => $additional_data->cc_exp_month,
                'anyoExpiracion' => substr($additional_data->cc_exp_year, 2, 2),
                'nombre' => $order->getCustomerName(),
                'apellidos' => $order->getCustomerLastname(),
                'cp' => $billingAddress->getPostcode(),
                'email' => $order->getCustomerEmail(),
                'telefono' => $billingAddress->getTelephone(),
                'celular' => $billingAddress->getTelephone(),
                'calleyNumero' => $billingAddress->getStreet()[0],
                'colonia' => $billingAddress->getStreet()[1],
                'municipio' => $billingAddress->getStreet()[2],
                'pais' => 'MEX', /* $billingAddress->getCountryId() - Tiene solo 2 caracteres */
                'estado' => $billingAddress->getRegion()
            ]
        ];
        
    }
    
    public function getTransactionInformation(){
        return $this->transaccion_data;
    }
}