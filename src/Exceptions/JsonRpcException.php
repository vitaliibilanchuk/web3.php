<?php

namespace Web3\Exceptions;

use RuntimeException;
use stdClass;

class JsonRpcException extends RuntimeException
{
    public ?string $data;
    public array $errorPayload;

    public function __construct(string $message = "", int $code = 0, ?string $data = null, array $errorPayload = [])
    {
        parent::__construct($message, $code);

        $this->data = $data;
        $this->errorPayload = $errorPayload;
    }

    public static function fromErrorObject(stdClass $error): self
    {
        $payload = get_object_vars($error);
        $message = isset($error->message) ? mb_ereg_replace('Error: ', '', (string) $error->message) : 'JSON-RPC error';
        $code = isset($error->code) ? (int) $error->code : 0;
        $data = isset($error->data) && is_string($error->data) ? $error->data : null;

        return new self($message, $code, $data, $payload);
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getErrorPayload(): array
    {
        return $this->errorPayload;
    }
}
