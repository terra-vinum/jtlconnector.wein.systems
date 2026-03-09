<?php

require __DIR__.'/vendor/autoload.php';

use Jtl\Connector\Core\Model as JTLModel;
use Jtl\Connector\Vivino\Application as VivinoApplication;
use Jtl\Connector\Vivino\Controller\CustomerOrderController;

$connectorDir = __DIR__;
VivinoApplication::init($connectorDir);
//
// $application = new VivinoApplication();
//
//


$qf=new JTLModel\QueryFilter();
// $qf->setLimit(10);
$c=new CustomerOrderController(VivinoApplication::pdo());
$c->pull($qf);
