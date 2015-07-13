<?php

namespace PHPFastCGI\Test\Slimmer;

use PHPFastCGI\Slimmer\AppWrapper;
use Slim\App;
use Slim\Exception\Exception as SlimException;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

/**
 * Tests the app wrapper.
 */
class AppWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that requests are passed through the wrapper correctly.
     */
    public function testHandleRequest()
    {
        // Create the Slim app
        $app = new App();

        // Add a simple route to the app
        $app->get('/hello/{name}', function ($request, $response, $args) {
            $response->write('Hello, ' . $args['name']);
            return $response;
        });

        // Create a kernel for the FastCGI daemon using the Slim app
        $kernel = new AppWrapper($app);

        // Create a request to test the route set up for the app
        $request = new ServerRequest([], [], '/hello/Andrew');

        // Get the response from the kernel that is wrapping the app
        $response = $kernel->handleRequest($request);

        // Check that the app has been wrapper properly by comparing to expected response
        $this->assertSame('Hello, Andrew', (string) $response->getBody());
    }

    /**
     * Tests that slim exception is handled correctly
     */
    public function testSlimException()
    {
        // Create the Slim app
        $app = new App();

        // Create a Slim Exception to throw
        $expectedResponse = new HtmlResponse('<h1>Error</h1>');
        $slimException = new SlimException($expectedResponse);

        // Add a simple route to the app that throws the exception
        $app->get('/hello/{name}', function ($request, $response, $args) use ($slimException) {
            throw $slimException;
        });

        // Create a kernel for the FastCGI daemon using the Slim app
        $kernel = new AppWrapper($app);

        // Create a request to test the route set up for the app
        $request = new ServerRequest([], [], '/hello/Andrew');

        // Get the response from the kernel that is wrapping the app
        $response = $kernel->handleRequest($request);

        // Check that the app has been wrapper properly by comparing to expected response
        $this->assertEquals((string) $expectedResponse->getBody(), (string) $response->getBody());
    }

    /**
     * Tests that an exception is handled correctly
     */
    public function testException()
    {
        // Create the Slim app
        $app = new App();

        // Add a simple route to the app that throws a general exception
        $app->get('/hello/{name}', function ($request, $response, $args) {
            throw new \Exception;
        });

        // Create a kernel for the FastCGI daemon using the Slim app
        $kernel = new AppWrapper($app);

        // Create a request to test the route set up for the app
        $request = new ServerRequest([], [], '/hello/Andrew');

        // Get the response from the kernel that is wrapping the app
        $response = $kernel->handleRequest($request);

        // Check that the app has returned a valid response
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
    }

    /**
     * Tests that content headers are stripped on 204/304 messages
     */
    public function testStrippedContentHeaders()
    {
        // Create the Slim app
        $app = new App();

        // Add a simple route to the app that throws a general exception
        $app->get('/hello/{name}', function ($request, $response, $args) {
            $response = new HtmlResponse('Hello');

            return $response
                ->withStatus(204)
                ->withAddedHeader('Content-Type', 'text/plain')
                ->withAddedHeader('Content-Length', (string) 10);
        });

        // Create a kernel for the FastCGI daemon using the Slim app
        $kernel = new AppWrapper($app);

        // Create a request to test the route set up for the app
        $request = new ServerRequest([], [], '/hello/Andrew');

        // Get the response from the kernel that is wrapping the app
        $response = $kernel->handleRequest($request);

        // Check that the app has returned a valid response
        $this->assertEquals('Hello', (string) $response->getBody());
        $this->assertFalse($response->hasHeader('Content-Type'));
        $this->assertFalse($response->hasHeader('Content-Length'));
    }
}
