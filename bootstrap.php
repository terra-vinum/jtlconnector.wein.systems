<?php
use Doctrine\ORM\Tools\Setup;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotation Mapping
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode, null, null, false);
// or if you prefer yaml or XML
// var_dump(glob(__DIR__."/config/yaml/*"));
// $config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);
// $config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);

$appConfig = json_decode(
    file_get_contents('config/config.json')
);


// database configuration parameters
$conn = array(
    'driver'   => 'pdo_mysql',
    'host'     => $appConfig->db->host,
    'user'     => $appConfig->db->username,
    'password' => $appConfig->db->password,
    'dbname'   => $appConfig->db->name,
);

// obtaining the entity manager
$entityManager = \Doctrine\ORM\EntityManager::create($conn, $config);
