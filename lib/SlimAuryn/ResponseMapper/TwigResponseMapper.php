<?php

declare(strict_types=1);

namespace SlimAuryn\ResponseMapper;

use Psr\Http\Message\ResponseInterface;
use SlimAuryn\Response\TwigResponse;
use Twig\Environment as TwigEnvironment;

class TwigResponseMapper
{
    /** @var TwigEnvironment */
    private $twig;

    /**
     * TwigResponseMapper constructor.
     * @param TwigEnvironment $twig
     */
    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(
        TwigResponse $twigResponse//,
//        ResponseInterface $originalResponse
    ): ResponseInterface {
        $html = $this->twig->render(
            $twigResponse->getTemplateName(),
            $twigResponse->getParameters()
        );

        $status = $twigResponse->getStatus();
//        $reasonPhrase = $this->getCustomReasonPhrase($status);

        $response = createResponse(
            $twigResponse->getStatus(),
            $this->getCustomReasonPhrase($status)
        );//$originalResponse->withStatus($status, $reasonPhrase);


        foreach ($twigResponse->getHeaders() as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }
        $response->getBody()->write($html);

        return $response;
    }

    public function getCustomReasonPhrase(int $status): string
    {
        $customStatusReasons = [
            420 => 'Enhance Your Calm',
        ];

        return $customStatusReasons[$status] ?? '';
    }
}
