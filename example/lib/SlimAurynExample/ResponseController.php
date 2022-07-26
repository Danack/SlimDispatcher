<?php

namespace SlimAurynExample;

use SlimAuryn\Response\HtmlResponse;
use Twig\Environment as TwigEnvironment;

class ResponseController
{
    public function getHomePage(TwigEnvironment $twig): HtmlResponse
    {
        $html = $twig->render('homepage.html');

        return new HtmlResponse($html);
    }

    public function sayHello(string $name): string
    {
        return "Hello there $name\n";
    }
}
