<?php

declare(strict_types=1);

namespace Pagofacil\Pagofacildirect\Model;

use DateTime;
use Generator;
use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Pagofacil\Pagofacildirect\Model\Payment;
use Pagofacil\Pagofacildirect\Model\Config\ConfigData;
use Pagofacil\Pagofacildirect\Model\Config\PagofacilConfigData;

class PagofacilConfigProvider implements ConfigProviderInterface
{
    /** 
     * @var array $methodCodes 
     */
    protected $methodCodes;
    
    /** 
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $methods;
    
    /** 
     * @var Pagofacil\Pagofacildirect\Model\Payment 
     */
    protected $payment;
    
    /** 
     * @var Cart
     */
    protected $cart;
    
    /** 
     * @var UrlInterface
     */
    private $urlInterface;
    
    private $pagofacilConfig;
    
    private $pagofacilConfigData;


    public function __construct(Data $data, Payment $payment, Cart $cart, ConfigData $pagofacilConfig)
    {
        
        $this->methods = [];
        $this->methodCodes = [
            Payment::CODE
        ];
        
        $this->cart = $cart;
        $this->payment = $payment;
        $this->pagofacilConfig = $pagofacilConfig;
        $this->pagofacilConfigData = new PagofacilConfigData();
        
        $this->methods[Payment::CODE] = $data->getMethodInstance(Payment::CODE);
        $this->urlInterface = ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');

    }
    
    /**
     * @return array
     * @throws Exception
     */
    public function getConfig()
    {
        if (!$this->methods[Payment::CODE]->isAvailable()) {
            return [];
        }
        
        return [
            'payment' => [
                'months_installments' => 'monthly_installments',
                'total' => $this->cart->getQuote()->getGrandTotal(),
                'branch_id' => $this->pagofacilConfig->getConfigDataPagofacil('display_user_branch_office_id'),
                'enable_3ds' => $this->pagofacilConfig->getConfigDataPagofacil('enable_3ds'),
                'threeds_uri' => $this->threeDsEndPointUri(),
                'transaction_3ds' => $this->threeDsTransactionData(),
                'i18n' => [
                    "Municipality" => "Municipio",
                    "Suburb" => "Colonia",
                    "Monthly installment" => "Meses sin intereses",
                    "Monthly payment" => "Pago mensual",
                ],
                'ccform' => [
                    'months' => [
                        Payment::CODE => $this->getMonths()
                    ],
                    'years' => [
                        Payment::CODE => $this->getYears()
                    ],
                    'cvvImageUrl' => [
                        Payment::CODE => $this->urlInterface->getUrl(
                            'pub/static/frontend/Magento/luma/es_MX/Magento_Checkout/'
                            ) .'cvv.png'
                    ],
                    'ssStartYears' => [
                        Payment::CODE => $this->getStartYear()
                    ],
                    'availableTypes' => [
                        Payment::CODE => [
                            "AE" => "American Express",
                            "VI" => "Visa",
                            "MC" => "MasterCard"
                        ]
                    ],
                    'hasVerification' => [
                        Payment::CODE => true
                    ],
                    'hasSsCardType' => [
                        Payment::CODE => false
                    ],
                ]
            ]
        ];
    }
    
    public function threeDsEndPointUri() {
        
        $threeds_uri = '';
        
        if($this->pagofacilConfig->getConfigDataPagofacil('enable_3ds')){
            if($this->pagofacilConfig->getConfigDataPagofacil('is_sandbox') == 1){
                $threeds_uri = $this->pagofacilConfig->getConfigDataPagofacil('endpoint_sandbox') . $this->pagofacilConfigData->getThreeDsEndPointUrl();
            } else {
                $threeds_uri = $this->pagofacilConfig->getConfigDataPagofacil('endpoint_production') . $this->pagofacilConfigData->getThreeDsEndPointUrl();
            }
        }
        
        return $threeds_uri;
    }
    
    private function threeDsTransactionData() {
        
        $enable_3ds = $this->pagofacilConfig->getConfigDataPagofacil('enable_3ds');
        
        if($this->pagofacilConfig->getConfigDataPagofacil('enable_3ds')){
            
            $transaction = $this->pagofacilConfigData->getEmpty3dsTransaction();
            $transaction['method'] = PagofacilConfigData::TRANSACTION_METHOD;
            $transaction['idUsuario'] = $this->pagofacilConfig->getConfigDataPagofacil('display_user_id');
            $transaction['idSucursal'] = $this->pagofacilConfig->getConfigDataPagofacil('display_user_branch_office_id');
            $transaction['Source'] = $this->pagofacilConfig->getConfigDataPagofacil('ecommerce_source');
            $transaction['idServicio'] = (int)3;
            $transaction['pais'] = 'MEX';

            $transaction_query = http_build_query($transaction, '', '&');

            return $transaction_query;
        } else {
            $transaction_query = array();
            return $transaction_query;
        }
        
    }
    
    public function getMonths(): array
    {
        return [
            "1" => "01 - Enero",
            "2" => "02 - Febrero",
            "3" => "03 - Marzo",
            "4" => "04 - Abril",
            "5" => "05 - Mayo",
            "6" => "06 - Junio",
            "7" => "07 - Julio",
            "8" => "08 - Agosto",
            "9" => "09 - Septiembre",
            "10"=> "10 - Octubre",
            "11"=> "11 - Noviembre",
            "12"=> "12 - Diciembre"
        ];
    }
    
    public function getYears(): array
    {
        $arrayYears = [];

        foreach ($this->yearGenerator() as $year) {
            $year = (string) $year;
            $arrayYears[$year] = $year;
        }

        return $arrayYears;
    }
    
    public function getStartYear(): array
    {
        $arrayYears = [];

        foreach ($this->startYearGenerator() as $year) {
            $year = (string) $year;
            $arrayYears[$year] = $year;
        }

        return $arrayYears;
    }
    
    protected function yearGenerator():Generator
    {
        $iterador = 0;
        $year = intval((new DateTime())->format('Y'));

        do{
            yield $year + $iterador;
            $iterador++;
        }while(10 >= $iterador);
    }
    
    protected function startYearGenerator():Generator
    {
        $year = intval((new DateTime())->format('Y'));

        for($iterador=5; $iterador>=0; $iterador--){
            yield ($year - $iterador);
        }
    }
    
}