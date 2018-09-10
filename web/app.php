<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
// Set the list of whitelisted IP adresses.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array(
    	'10.102.2.1',    	// front01
        '10.102.2.2',    	// front02
        '10.102.2.11',    	// api01
        '10.102.2.12',    	// api02
        '10.102.2.21',    	// encoding01
        '10.102.2.22',    	// encoding02
        '10.102.2.23',    	// encoding03
        '10.102.2.31',    	// db01
        '10.102.2.254',    	// gateway
        '37.61.243.70',    	// floating public ip
        '37.61.243.71',    	// public ip 1
        '37.61.243.72',    	// public ip 2
    )) || php_sapi_name() === 'cli-server')
) {
    header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://www.strime.io");
}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.
/*
$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
