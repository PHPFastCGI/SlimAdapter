# Slimmer

A PHP package which allows Slim v3 applications to reduce overheads by exposing their Request-Response structure to a FastCGI daemon.

## Introduction

Using this package, Slim v3 applications can stay alive between HTTP requests whilst operating behind the protection of a FastCGI enabled web server.

## Usage

```php
<?php // command.php

// Include the composer autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use PHPFastCGI\FastCGIDaemon\Command\DaemonRunCommand;
use PHPFastCGI\FastCGIDaemon\DaemonFactory;
use PHPFastCGI\Slimmer\AppWrapper;
use Slim\App as SlimApp;
use Symfony\Component\Console\Application as ConsoleApplication;

// Create your Slim app
$app = new SlimApp();
$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->write('Hello, ' . $args['name']);
    return $response;
});

// Dependency 1: The daemon factory
$daemonFactory = new DaemonFactory;

// Dependency 2: A kernel for the FastCGIDaemon library (from the Slim app)
$kernel = new AppWrapper($app);

// Create an instance of DaemonRunCommand using the daemon factory and the kernel
$command = new DaemonRunCommand('run', 'Run a FastCGI daemon', $daemonFactory, $kernel);

// Create a symfony console application and add the command
$consoleApplication = new ConsoleApplication;
$consoleApplication->add($command);

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

## Current Status

This library is currently in early development stages and not considered stable. A stable release is expected by September 2015.

Contributions and suggestions are welcome

