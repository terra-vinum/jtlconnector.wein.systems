<?php

$connectorDir = dirname(dirname(__DIR__));

require_once $connectorDir . "/vendor/autoload.php";

putenv("JTL_ROOT_DIR=$connectorDir");

use Jtl\Connector\Vivino\FeedApplication;

FeedApplication::init($connectorDir);
FeedApplication::get()->run();
