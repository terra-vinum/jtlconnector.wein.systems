<?php

$connectorDir = __DIR__;

require_once $connectorDir . "/vendor/autoload.php";

use Jtl\Connector\Core\Config\FileConfig;
use Jtl\Connector\Core\Model;

use Jtl\Connector\Vivino\Controller;
use Jtl\Connector\Vivino\Model\ModelManager;

$config = new FileConfig(sprintf('%s/config/config.json', $connectorDir));

$pdo = new PDO(
	sprintf("mysql:host=%s;dbname=%s", $config['db']["host"], $config['db']["name"]),
	$config['db']["username"],
	$config['db']["password"]
);
$mgr = new Jtl\Connector\Vivino\ModelManager($pdo);

$ctrl = new Controller\ProductController($pdo);

$pulled = $ctrl->pull(new Model\QueryFilter());
var_dump($pulled);

$pushed = $ctrl->push(new Model\Product());

// $model = new Model\Product();
// $mgr->persist($model);


// use Metadata\MetadataFactory;
// use Metadata\Driver\DriverChain;
// use JMS\Serializer\Metadata\Driver;
// use Doctrine\Common\Annotations;
// use JMS\Serializer\Naming;
// use JMS\Serializer\Type;
// use JMS\Serializer\Expression;
// 
// $reader = new Annotations\SimpleAnnotationReader();
// $naming = new Naming\IdenticalPropertyNamingStrategy();
// 
// $driver = new DriverChain(array(
// 	// new Driver\AnnotationDriver($reader, $naming),
// 	new Driver\AnnotationOrAttributeDriver($naming),
// /** Annotation, YAML, XML, PHP, ... drivers */
// ));
// $factory = new MetadataFactory($driver);
// $metadata = $factory->getMetadataForClass('Jtl\Connector\Core\Model\Product');
// 
// var_dump($metadata->propertyMetadata['nextAvailableInflowDate']);
// foreach ( $metadata->propertyMetadata['masterProductId'] as $prop => $md ) {
// 	break;
// }

// var_dump($metadata);