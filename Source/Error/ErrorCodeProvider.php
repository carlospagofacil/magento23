<?php
	
namespace PagoFacil\Pagofacildirect\Source\Error;

class ErrorCodeProvider
{
    /**
     * Retrieves list of error codes from Braintree response.
     *
     * @param Successful|Error $response
     * @return array
     */
    public function getErrorCodes($response): array
    {
        $result = [];
        if (!$response instanceof Error) {
            return $result;
        }

        /** @var ErrorCollection $collection */
        $collection = $response->errors;

        /** @var Validation $error */
        foreach ($collection->deepAll() as $error) {
            $result[] = $error->code;
        }

        return $result;
    }
}
