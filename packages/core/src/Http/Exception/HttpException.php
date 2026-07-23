<?php

declare(strict_types=1);

namespace MaxServ\Core\Http\Exception;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
