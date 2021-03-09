<?php

declare(strict_types=1);

namespace PagoFacil\Pagofacildirect\Exceptions;

use Exception;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractException extends LocalizedException
{
    /** @var string $exceptionCode */
    private $exceptionCode;

    static protected $error_standard = 'no_exception_event';

    /**
     * AbstractException constructor.
     * @param string $phrase
     * @param int $code
     * @param Exception|null $cause
     */
    public function __construct(string $phrase, $code = 0, Exception $cause = null)
    {
        parent::__construct(__($phrase), $cause, $code);
        $this->setExceptionCode(static::$error_standard);
    }

    /**
     * @return string
     */
    public function getExceptionCode(): string
    {
        return $this->exceptionCode;
    }

    /**
     * @param string $code
     */
    protected function setExceptionCode(string $code): void
    {
        $this->exceptionCode = $code;
    }
}
