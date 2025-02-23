<?php

namespace App\Exceptions;

use Exception;

class ExchangeException extends \Exception
{
    protected $exchange;
    protected $symbol;
    protected $rawResponse;

    public function __construct(
        string $message,
        string $exchange = null,
        string $symbol = null,
        $rawResponse = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->exchange = $exchange;
        $this->symbol = $symbol;
        $this->rawResponse = $rawResponse;
    }

    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }
}