<?php

namespace PHPFastCGI\Test\Slim;

use PHPFastCGI\Adapter\Slim\AppWrapper;
use PHPFastCGI\FastCGIDaemon\Http\Request;
use Slim\App;
use Zend\Diactoros\Response\HtmlResponse;

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
        $stream = fopen('php://temp', 'r');
        $request = new Request(['REQUEST_URI' => '/hello/Andrew'], $stream);

        // Get the response from the kernel that is wrapping the app
        $response = $kernel->handleRequest($request);

        // Check that the app has been wrapper properly by comparing to expected response
        $this->assertSame('Hello, Andrew', (string) $response->getBody());

        fclose($stream);
    }
}
