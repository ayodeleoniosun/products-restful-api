<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomException extends HttpException
{
    public function __construct(
        string $message = '',
        int $statusCode = Response::HTTP_BAD_REQUEST,
    ) {
        parent::__construct($statusCode, $message);
    }
}