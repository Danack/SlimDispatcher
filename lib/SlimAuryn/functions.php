<?php

declare(strict_types = 1);

namespace SlimAuryn;

use Psr\Http\Message\ResponseInterface;

// Define a function that writes a string into the response object.
function convertStringToHtmlResponse(string $result, ResponseInterface $response): ResponseInterface
{
    $response = $response->withHeader('Content-Type', 'text/html');
    $response->getBody()->write($result);
    return $response;
}
