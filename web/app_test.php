<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
// Set the list of whitelisted IP adresses.
/* if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array(
    	'93.95.239.182',    // Office
    	'109.0.254.200',    // 4G box
    	'80.215.234.146',   // La Cordée Opéra
    	'82.239.194.91',    // Home
    	'88.166.82.213',    // Suzette
    	'78.250.175.242',   // Bar Le Renaissance in Lyon
        '80.215.174.68',    // iPhone Bouygues
        '80.215.225.86',    // iPhone Bouygues
        '178.62.32.72',     // Strime - Serveur dev
        '80.15.155.212',    // La Cordée Liberté
        '89.3.187.25',      // La Cordée Gare de Lyon
        '109.8.173.218',    // La Cordée Rennes
        '84.14.224.162',    // ILB, réseau CEC
        '77.154.204.238',   // iPhone, Red de SFR
        '193.248.62.167',   // La Cordée Perrache
        '192.168.1.151',    // La Cordée Perrache
        '207.253.195.3',    // Centre des Congrès de Québec
        '69.70.206.106',    // Le Terminal, WAQ, Québec
        '69.70.206.102',    // Le Terminal, WAQ, Québec
        '69.70.206.122',    // Le Terminal, WAQ, Québec
        '80.215.138.233',   // Boitier 3G
        '88.177.58.105',    // Vezin
        '31.38.180.13',     // Alençon
        '167.114.240.38',   // Serveur VPS OVH
        '167.114.232.49',   // Serveur Encoding Bobby
        '167.114.237.150',   // Serveur Encoding Franck
        '167.114.237.154',   // Serveur Encoding JP
    )) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
} */

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
