<?php

$connectorDir = dirname(__DIR__);

require_once $connectorDir . "/vendor/autoload.php";

putenv("JTL_ROOT_DIR=$connectorDir");

use Jtl\Connector\Vivino\Application;
use Jtl\Connector\Vivino\Connector;

/*
// grab i/o for debugging
ob_start(function($response) {
    $t=time();
    $request = var_export(getallheaders(),true)
        . "\n\n"
        . urldecode(file_get_contents('php://input'));
    file_put_contents( $t . '.request.txt',$request);
    file_put_contents( $t . '.response.txt',$response);
    return $response;
});
//*/

//Instantiating and starting the Application as the highest instance of the connector
Application::init(dirname(__DIR__));
Application::get()->run(new Connector());
