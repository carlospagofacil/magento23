<?php

namespace PagoFacil\Pagofacildirect\Source\Api;

class Charge {

    private $id;
    
    private $orderId;
    
    private $message;
    
    private $cctype;
    
    private $ccdigit;
    
    private $statusCode;
    
    private $code;
    
    /**
     * Charge constructor.
     * @param string $id
     * @param string $orderId
     * @param string $message
     */
    public function __construct(string $id, string $orderId, string $message, string $cctype, string $ccdigit)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->message = $message;
        $this->cctype = $cctype;
        $this->ccdigit = $ccdigit;
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCctype()
    {
        return $this->cctype;
    }

    /**
     * @return string
     */
    public function getCcdigits()
    {
        return substr($this->ccdigit, -4);
    }
    
    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param Charge $charge
     * @param int $code
     * @param string $statusCode
     * @return static
     */
    public function setCode(int $code, string $statusCode)
    {
        //$charge->code = $code;
        //$charge->statusCode = $statusCode;

        //return $charge;
    }
    
}