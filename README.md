# PHPFastCGI Slim Adapter

[![Latest Stable Version](https://poser.pugx.org/phpfastcgi/slim-adapter/v/stable)](https://packagist.org/packages/phpfastcgi/slim-adapter)
[![Build Status](https://travis-ci.org/PHPFastCGI/SlimAdapter.svg?branch=master)](https://travis-ci.org/PHPFastCGI/SlimAdapter)
[![Coverage Status](https://coveralls.io/repos/PHPFastCGI/SlimAdapter/badge.svg?branch=master&service=github)](https://coveralls.io/github/PHPFastCGI/SlimAdapter?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PHPFastCGI/SlimAdapter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PHPFastCGI/SlimAdapter/?branch=master)
[![Total Downloads](https://poser.pugx.org/phpfastcgi/slim-adapter/downloads)](https://packagist.org/packages/phpfastcgi/slim-adapter)

A PHP package which allows Slim v3 applications to reduce overheads by exposing their Request-Response structure to a FastCGI daemon.

Visit the [project website](http://phpfastcgi.github.io/).

## Introduction

Using this package, Slim v3 applications can stay alive between HTTP requests whilst operating behind the protection of a FastCGI enabled web server.

## Current Status

This project is currently in early stages of development and not considered stable. Importantly, this library currently lacks support for uploaded files.

There are currently [two known issues for this adapter](#known-issues).

Contributions and suggestions are welcome.

## Installing

```sh
composer require "phpfastcgi/slim-adapter:^0.6"
```

## Usage

```php
<?php // command.php

// Include the composer autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use PHPFastCGI\FastCGIDaemon\ApplicationFactory;
use PHPFastCGI\Adapter\Slim\AppWrapper;
use Slim\App;

// Create your Slim app
$app = new App();
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

## Known Issues

There are two known issues with this adapter that will be fixed in later versions.

1. Some Slim applications make use of additional helper methods on the Slim PSR-7 request object. Currently, PHPFastCGI uses request objects created by [Diactoros](https://github.com/zendframework/zend-diactoros) that do not have these helper methods. It is likely that some middleware that switches the Diactoros request to a Slim request will be created in the not too distant future.

2. Slim still keeps the request and response objects in the container. These container entries will not be valid for Slim applications running under PHPFastCGI. The request and response should always be received as method parameters.
