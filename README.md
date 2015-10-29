# Slimmer

[![Build Status](https://travis-ci.org/PHPFastCGI/Slimmer.svg?branch=master)](https://travis-ci.org/PHPFastCGI/Slimmer)
[![Coverage Status](https://coveralls.io/repos/PHPFastCGI/Slimmer/badge.svg?branch=master)](https://coveralls.io/r/PHPFastCGI/Slimmer?branch=master)

A PHP package which allows Slim v3 applications to reduce overheads by exposing their Request-Response structure to a FastCGI daemon.

Visit the [project website](http://phpfastcgi.github.io/).

## Introduction

Using this package, Slim v3 applications can stay alive between HTTP requests whilst operating behind the protection of a FastCGI enabled web server.

## Current Status

This project is currently in early stages of development and not considered stable. Importantly, this library currently lacks support for uploaded files. Also a memory leak protection feature is scheduled for integration that allows the daemon to shutdown after handling 'N' requests.

Contributions and suggestions are welcome.

## Installing

```sh
composer require "phpfastcgi/slimmer:^0.4"
```

## Usage

```php
<?php // command.php

// Include the composer autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use PHPFastCGI\FastCGIDaemon\ApplicationFactory;
use PHPFastCGI\Slimmer\AppWrapper;
use Slim\App as SlimApp;

// Create your Slim app
$app = new SlimApp();
$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->write('Hello, ' . $args['name']);
    return $response;
});

// Create the kernel for the FastCGIDaemon library (from the Slim app)
$kernel = new AppWrapper($app);

// Create the symfony console application
$consoleApplication = (new ApplicationFactory)->createApplication($kernel);

// Run the symfony console application
$consoleApplication->run();
```

If you wish to configure your FastCGI application to work with the apache web server, you can use the apache FastCGI module to process manage your application.

This can be done by creating a FastCGI script that launches your application and inserting a FastCgiServer directive into your virtual host configuration.

```sh
#!/bin/bash
php /path/to/command.php run
```

```
FastCgiServer /path/to/web/root/script.fcgi
```

If you are using a web server such as nginx, you will need to use a process manager to monitor and run your application.
