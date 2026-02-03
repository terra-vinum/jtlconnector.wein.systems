<?php

namespace Jtl\Connector\Vivino;

use PDO;
use Datetime;
use Metadata\MetadataFactory;
use Metadata\Driver\DriverChain;
use JMS\Serializer\Metadata\Driver;
use Doctrine\Common\Annotations;
use JMS\Serializer\Naming;
use JMS\Serializer\Type;
use JMS\Serializer\Expression;
use Ramsey\Uuid\Uuid;

use Jtl\Connector\Core\Model;
use Jtl\Connector\Core\Utilities;

use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Inflector\InflectorInterface;

class ModelManager
{
	private static $instance = null;
	private PDO $pdo;
	private InflectorInterface $inflector;
	private MetadataFactory $metadataFactory;

	public static function get(PDO $pdo) {
		if ( is_null(self::$instance ) ) {
			self::$instance = new self($pdo);
		}
		return self::$instance;
	}

	public function __construct(PDO $pdo)
	{
		$this->pdo             = $pdo;
		$this->metadataFactory = new MetadataFactory(new DriverChain([
			new Driver\AnnotationOrAttributeDriver(
				new Naming\IdenticalPropertyNamingStrategy()
			)
		]));
		$this->inflector       = new EnglishInflector();
	}

	public function parseDbData(Model\AbstractModel $model, $data) {
		$metadata  = $this->getMetadata($modelClass);
		$modeldata = [];
		$id        = null;
		foreach ( get_object_vars($data) as $key => $value ) {
			$prop = Utilities\Str::toPascalCase($key);
			$meta = $metadata->propertyMetadata[$prop];
			$type  = $meta->type['name'];

			if ( 'Jtl\Connector\Core\Model\Identity' === $type ) {
				$data->$key = new Model\Identity($value);
				$model->{$meta->setter}($data->$key);

			} else if ( 'DateTimeInterface' === $type ) {
				$data->$key = $value
					? DateTime::createFromFormat( 'Y-m-d H:i:s', $value )
					: null;
				$model->{$meta->setter}($data->$key);
			} else if ( class_exists($type) ) {
				
			} else if ( 'boolean' === $type ) {
				$data->$key = (bool) $value;
				$model->{$meta->setter}($data->$key);
			} else if ( 'integer' === $type ) {
				$data->$key = (int) $value;
				$model->{$meta->setter}($data->$key);
			} else if ( 'double' === $type ) {
				$data->$key = (float) $value;
				$model->{$meta->setter}($data->$key);
			} else if ( 'string' === $type ) {
				$data->$key = (string) $value;
				$model->{$meta->setter}($data->$key);
			}
		}
		return $model;
	}
	// 
	// public function persist(Model\AbstractModel $model, $table, array $columns ) {
	// 	$values = [];
	// 	foreach ( $columns as $key ) {
	// 		$prop = Utilities\Str::toPascalCase($key);
	// 		$meta = $metadata->propertyMetadata[$prop];
	// 		$type  = $meta->type['name'];
	// 
	// 		$value = $model->{$meta->getter}();
	// 		if ( 'Jtl\Connector\Core\Model\Identity' === $type ) {
	// 			$values[$key] = $value->getEndpoint();
	// 
	// 		} else if ( 'DateTimeInterface' === $type ) {
	// 			$values[$key] = $value->format('Y-m-d H:i:s');
	// 		} else if ( class_exists($type) ) {
	// 
	// 		} else if ( 'boolean' === $type ) {
	// 			$values[$key] = (int) $value;
	// 
	// 		} else if ( 'integer' === $type ) {
	// 			$values[$key] = (int) $value;
	// 
	// 		} else if ( 'double' === $type ) {
	// 			$values[$key] = (float) $value;
	// 
	// 		} else if ( 'string' === $type ) {
	// 			$values[$key] = (string) $value;
	// 		}
	// 	}
	// 
	// 	$columns      = implode(', ',array_keys( $values ));
	// 	$placeholders = implode(', ',array_map(function($col) {
	// 		return ":{$col}";
	// 	},array_keys( $values )));
	// 	$updates      = implode(', ',array_map(function($col) {
	// 		return "$col = :{$col}";
	// 	},array_keys( $values )));
	// 
	// 	$sql = "INSERT INTO {$table} ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE {$updates}";
	// 
	// }


	// 
	// public function fetch( $class, $id ) {
	// 	$table = $this->getTableName($model);
	// 
	// }
	// 
	// private function getTableName($modelClass) {
	// 	preg_match('@[a-z0-9]+$@iU',$modelClass,$matches);
	// 	return $this->inflector->pluralize(Utilities\Str::toSnakeCase($matches[0]))[0];
	// }

	public function getMetadata($modelClass) {
		return $this->metadataFactory->getMetadataForClass($modelClass);
	}
	
}