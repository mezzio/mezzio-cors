# mezzio-cors

[![Build Status](https://travis-ci.org/mezzio/mezzio-cors.svg?branch=master)](https://travis-ci.org/mezzio/mezzio-cors)
[![Coverage Status](https://coveralls.io/repos/github/mezzio/mezzio-cors/badge.svg?branch=master)](https://coveralls.io/github/mezzio/mezzio-cors?branch=master)

CORS subcomponent for [Mezzio](https://github.com/mezzio/mezzio).


This extension creates CORS details for your application. If the `CorsMiddleware` detects a `CORS preflight`, the middleware will start do detect the proper `CORS` configuration.
The `Router` is being used to detect every allowed request method by executing a route match with all possible request methods. Therefore, for every preflight request, there is at least one `Router` request (depending on the configuration of the route, it might be just one or we are executing a check for **every** request method).

Here is a list of the request methods being checked for the `CORS preflight` information:

- DELETE
- GET
- HEAD
- OPTIONS
- PATCH
- POST
- PUT
- TRACE

The order of the headers might vary, depending on what request method is being requested with the `CORS preflight` request.
In the end, the response contains **every** possible request method of the route due to what the router tells the `ConfigurationLocator`.


The allowed origins can be configured as strings which can be matched with [`fnmatch`](https://www.php.net/manual/en/function.fnmatch.php). Therefore, wildcards are possible.

## Installation

```bash
$ composer require mezzio/mezzio-cors
```

## Features

mezzio-cors provides a [`CorsMiddleware`](middleware.md) which works out of the box with once created a global configuration file. It can safely be added to the projects pipeline as CORS details are needed for *every* request (in case its a CORS request).

It uses the [mezzio-router](mezzio/mezzio-router) to match the incoming URI. It starts with the HTTP request method which is provided by the Request via the `Access-Control-Request-Method` header and checks *all* request methods until it matches a route. If that route states to be explicit, the response is created immediately.

If the route is not explicit, *all* request methods are checked to provide a list of possible request methods to the client.
