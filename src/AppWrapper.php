<?php

namespace PHPFastCGI\Slimmer;

use PHPFastCGI\FastCGIDaemon\Http\RequestInterface;
use PHPFastCGI\FastCGIDaemon\KernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Container;
use Slim\Exception\Exception as SlimException;
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
     * @var Container
     */
    private $container;

    /**
     * Constructor.
     * 
     * @param App $app The Slim v3 application object to wrap
     */
    public function __construct(App $app)
    {
        $this->app       = $app;
        $this->container = $app->getContainer();

        $this->container->get('router')->finalize();
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request)
    {
        $serverRequest = $request->getServerRequest();

        $headers  =  new Headers(['Content-Type' => 'text/html']);
        $response = (new Response(200, $headers))->withProtocolVersion('1.1');

        try {
            $response = $this->app->callMiddlewareStack($serverRequest, $response);
        } catch (SlimException $exception) {
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            $errorHandler = $this->container->get('errorHandler');
            $response = $errorHandler($serverRequest, $response, $exception);
        }

        return $this->finalizeResponse($response);
    }

    /**
     * Finalizes the applications response, similar to Slim\App::respond()
     * 
     * @param ResponseInterface $response
     * 
     * @return ResponseInterface
     */
    private function finalizeResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 204 && $statusCode !== 304) {
            $bodySize = $response->getBody()->getSize();

            if (null !== $bodySize) {
                $response = $response->withHeader('Content-Length', (string) $bodySize);
            }
        } else {
            $response = $response->withoutHeader('Content-Type')->withoutHeader('Content-Length');
        }

        return $response;
    }
}
