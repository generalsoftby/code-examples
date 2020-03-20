<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApiException.
 */
class ApiException extends HttpException
{
    /**
     * @var array
     */
    protected $errorData;

    /**
     * ApiException constructor.
     *
     * @param array  $errorData  Exception data
     * @param string $message    Message
     * @param int    $statusCode Code
     */
    public function __construct(array $errorData, string $message = '', int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct($statusCode, $message);

        $this->errorData = $errorData;
    }

    /**
     * Formatted api response about exception.
     *
     * @return array
     */
    public function getExceptionDetails(): array
    {
        return [
            'code' => $this->getStatusCode(),
            'message' => $this->getMessage(),
            'errorData' => $this->getErrorData(),
        ];
    }

    /**
     * Info about error.
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }
}
