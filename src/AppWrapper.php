<?php

namespace PHPFastCGI\Adapter\Slim;

use PHPFastCGI\FastCGIDaemon\Http\RequestInterface;
use PHPFastCGI\FastCGIDaemon\KernelInterface;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Response;

/**
 * Wraps a Slim v3 application object as an implementation of the kernel
 * interface.
 */
class AppWrapper implements KernelInterface
{
    /**
     * @var App
     */
    private $app;

    /**
     * Constructor.
     * 
     * @param App $app The Slim v3 application object to wrap
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request)
    {
        $serverRequest = $request->getServerRequest();

        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $response = new Response(200, $headers);
        $response = $response->withProtocolVersion('1.1');

        return $this->app->process($serverRequest, $response);
    }
}
