<?php

$connectorDir = dirname(__DIR__);

require_once $connectorDir . "/vendor/autoload.php";

putenv("JTL_ROOT_DIR=$connectorDir");

use Jtl\Connector\Vivino\Application;
use Jtl\Connector\Vivino\Connector;


//Setting up a custom FileConfig passing the needed File

//Instantiating and starting the Application as the highest instance of the connector
Application::init(dirname(__DIR__));
Application::get()->run(new Connector());
