<?php

namespace Pagofacil\Pagofacildirect\Source\Api;

use Pagofacil\Pagofacildirect\Source\Client\ResponseClient;
use PagoFacil\Pagofacildirect\Exceptions\HttpException;
use PagoFacil\Pagofacildirect\Exceptions\PaymentException;
use Pagofacil\Pagofacildirect\Source\Logger\PagofacilLog;
use PagoFacil\Pagofacildirect\Source\Api\Charge;
use Psr\Log\LoggerInterface;

class ResponseApi {
    
    private $status_code;
    
    private $body;
    
    private $transaction_body;
    
    private $charge;

    /**
     * Response constructor.
     * @param response $body
     * @param int $statusCode
     */
    public function __construct(string $body, int $status_code)
    {
        $this->body = $body;
        $this->status_code = $status_code;
        
        $this->parseJsonToArray();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        $logger->info('ResponseApi - $body: ');
        $logger->info(print_r($body, true));
        $logger->info('ResponseApi - $status: ');
        $logger->info(print_r($status_code, true));

        $this->validateTransactionData();
        
    }

    protected function parseJsonToArray(){
        $arrayResponse = json_decode($this->body, true);
        $this->transaction_body = $arrayResponse['WebServices_Transacciones'];
    }
    
    
    protected function validateTransactionData() {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pagofacil_card.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        if($this->validateStatusCodeRange()
            && $this->getStatusCodeText()
            && $this->validateTransaction()
            && $this->validateAuthorized()){

            $logger->info('ResponseApi Paso las Validaciones: ');
            $this->createCharge();
        } else {
            $logger->info('ResponseApi Else NO Paso las Validaciones: ');
            $this->createEmptyCharge();
            new \Magento\Framework\Validator\Exception(__('Payment Refunding error 00002.'));
        }
    }
    
    /**
     * @param int $code
     * @throws InvalidArgumentException
     */
    protected function validateStatusCodeRange(){
        
        $validate = true;
        if (100 > $this->status_code || 600 <= $this->status_code) {
            $validate = false;
            PagofacilLog::error('Error en la respuesta de la transacción');
        }
        
        return $validate;
    }
    
    /**
     * @param int $statusCode
     * @return string
     * @throws HttpException
     */
    public function getStatusCodeText(){
        
        $validate = true;
        if (!array_key_exists($this->status_code, ResponseClient::PHRASES)) {
            $validate = false;
            PagofacilLog::error('Error en el código de la transacción');
        }

        return $validate;
    }

    /**
     * @throws PaymentException
     * @throws \Exception
     */
    public function validateAuthorized(){
        
        $validate = true;
        if (!array_key_exists('autorizado', $this->transaction_body['transaccion']) 
            || $this->transaction_body['transaccion']['autorizado'] == 0) {
            
            $validate = false;
            PagofacilLog::error('La transacción es denegada');
        }
        
        return $validate;
    }

    /**
     * @throws ClientException
     */
    protected function validateTransaction(){
        
        $validate = true;
        if(!array_key_exists('idTransaccion', $this->transaction_body['transaccion'])) {
            $validate = false;
            PagofacilLog::error('La transacción no fue exitosa');
        }
        
        return $validate;
    }

    /**
     * @return int
     */
    public function getStatusCode(){
        return $this->status_code;
    }

    /**
     * @return mixed|\Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getBodyToArray()
    {
        return $this->transaction_body;
    }

    private function createCharge()
    {
        
        $this->charge = new Charge(
            $this->transaction_body['transaccion']['idTransaccion'],
            $this->transaction_body['transaccion']['data']['idPedido'],
            $this->transaction_body['transaccion']['pf_message'],
            $this->transaction_body['transaccion']['TipoTC'],
            $this->transaction_body['transaccion']['dataVal']['numeroTarjeta']
        );
    }
 
    private function createEmptyCharge() {
        
        $this->charge = new Charge(0, 0, '', '', '');
    }
    
    public function charge() {
        return $this->charge;
    }
}