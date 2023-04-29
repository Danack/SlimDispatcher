<?php

namespace SlimDispatcher\Response;

use SlimDispatcher\Response\StubResponse;
use SlimDispatcher\Response\InvalidDataException;

class JsonResponse implements StubResponse
{
    private $body;

    private $headers = [];

    private $status;

    public function getStatus() : int
    {
        return $this->status;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * JsonResponse constructor.
     * @param mixed $data
     * @param array $headers
     * @param int $status
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function __construct($data, array $headers = [], int $status = 200)
    {
        $standardHeaders = [
            'Content-Type' => 'application/json'
        ];

        $this->headers = array_merge($standardHeaders, $headers);
        $this->status = $status;
        $this->body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($this->body === false) {
            $message = sprintf(
                "Failed to convert array to JSON with error %s:%s",
                json_last_error(),
                json_last_error_msg()
            );

            throw new InvalidDataException($message);
        }
    }

    public function getBody() :string
    {
        return $this->body;
    }
}
