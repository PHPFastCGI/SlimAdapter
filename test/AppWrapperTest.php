<?php

namespace PHPFastCGI\Test\Slimmer;

use PHPFastCGI\Slimmer\AppWrapper;
use Slim\App;
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
}
