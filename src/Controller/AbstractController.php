<?php

namespace Jtl\Connector\Vivino\Controller;

use PDO;
use Datetime;

use Doctrine\Common\Annotations;

use Jtl\Connector\Core\Logger\LoggerService;
use Jtl\Connector\Core\Model;
use Jtl\Connector\Core\Utilities;
use Jtl\Connector\Vivino;

use JMS\Serializer\Metadata\Driver;
use JMS\Serializer\Naming;
use JMS\Serializer\Type;
use JMS\Serializer\Expression;

use Monolog\Logger as MonoLogger;

use Metadata\MetadataFactory;
use Metadata\Driver\DriverChain;

use Ramsey\Uuid\Uuid;


/**
 * Abstract controller class to pass the database object only once.
 *
 * Class AbstractController
 * @package Jtl\Connector\Vivino\Controller
 */
abstract class AbstractController
{
    /**
     * @var PDO
     */
	protected PDO $pdo;
	protected Vivino\ModelManager $manager;
    protected MonoLogger $loggerService;

	protected string $modelClass;
	protected string $table;
	protected array $id_columns;
	protected array $columns;

	/**
	 * Using direct dependencies for better testing and easier use with a DI container.
	 *
	 * AbstractController constructor.
	 * @param PDO $pdo
	 */
	public function __construct(PDO $pdo)
	{
		$this->pdo     = $pdo;
		$this->manager = Vivino\ModelManager::get($this->pdo);
	}


	public function persist(Model\AbstractModel $model ) {
		$this->persistValues(
			$this->getModelValues( $model, $this->columns ),
			$this->table, 
			$this->columns, 
			$this->id_columns
		);
		
		return $model;
	}

	protected function getModelValues( $model, $columns ) {
		$metadata = $this->manager->getMetadata(get_class($model));
		$values   = [];
		foreach ( $columns as $key ) {
			$prop = Utilities\Str::toCamelCase($key);

			if ( ! isset( $metadata->propertyMetadata[$prop] ) ) {
				continue;
			}
			$meta = $metadata->propertyMetadata[$prop];
			$type  = $meta->type['name'];
			$value = $model->{$meta->getter}();
			if ( 'Jtl\Connector\Core\Model\Identity' === $type ) {
				if ( in_array( $key, $this->id_columns )) {
					
					$endpointId = $value->getEndpoint();

					if (empty($endpointId)) {
						$endpointId = Uuid::uuid4()->getHex()->toString();
						$value->setEndpoint($endpointId);
					}
				}
				$values[$key] = $value->getEndpoint();

			} else if ( 'DateTimeInterface' === $type && ($value instanceof $type) ) {
				$values[$key] = $value->format('Y-m-d H:i:s');

			} else if ( class_exists($type) ) {

			} else if ( 'boolean' === $type ) {
				$values[$key] = (int) $value;

			} else if ( 'integer' === $type ) {
				$values[$key] = (int) $value;

			} else if ( 'double' === $type ) {
				$values[$key] = (float) $value;

			} else if ( 'string' === $type ) {
				$values[$key] = (string) $value;
			}
		}

		return $values;
	}

	protected function persistValues( $values, $table, $columns, $id_columns ) {

		$columns      = implode(', ',array_keys( $values ));
		$placeholders = implode(', ',array_map(function($col) {
			return ":{$col}";
		},array_keys( $values )));
		$updates      = implode(', ',array_map(function($col) {
			return "$col = :{$col}";
		},array_diff(array_keys( $values ), $this->id_columns )));

		$sql = "INSERT INTO {$table} ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE {$updates}";
// if(isset($values['language_iso'])) throw new \Exception(var_export($sql,true));

		$this->pdo->prepare($sql)->execute($values);

	}


    protected function fetchModel($modelClass, string $table, $id ) {
        $props = $this->pdo->prepare("SELECT * FROM {$table} WHERE id = ?",[$id])->fetch(PDO::FETCH_ASSOC);
        $model = new $modelClass();
        foreach ( $props as $prop => $value ) {
            $getter = 'get' . Utilities\Str::toCamelCase($prop);
            $setter = 'set' . Utilities\Str::toCamelCase($prop);
            if ( is_callable( [$model, $setter] )) {
                $fakeValue = $model->$getter($value);
                if ( is_scalar( $fakeValue ) ) {
                    
                }
                $model->$setter($value);
            }
        }
    }

    protected function persistI18n(Model\AbstractI18n $i18n, string $table, array $props ) {
        $endpointId = $i18n->getId()->getEndpoint();
        $parts      = $this->makeStatementParts($i18n,$props);
        $query      = "INSERT INTO {$table} (model_id, language_iso, {$parts->columns}) VALUES (?, ?, {$parts->placeholders}) ON DUPLICATE KEY UPDATE {$parts->updates}";
        $params     = [
            $endpointId,
            $i18n->getLanguageIso(),
            ...$parts->values,
            ...$parts->values
        ];

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
    }

    protected function makeStatementParts(Model\AbstractModel $model, array $props) {
        return (object) [
            'columns'      => implode( ', ', $props ),
            'placeholders' => implode( ', ', array_map(function($prop) { return '?'; }, $props ) ),
            'updates'      => implode( ', ', array_map(function($prop) { return $prop . ' = ?'; }, $props ) ),
            'values'       => array_map( function($prop) use ($model) {
                $getter = 'get' . Utilities\Str::toCamelCase($prop);
                if ( is_callable( [ $model, $getter ] ) ) {
                    $value = $model->$getter();
                    if ( ! is_scalar( $value ) ) {
                        if ( is_object($value ) ) {
                            if ( $value instanceof Datetime ) {
                                $value = $value->format('Y-m-d H:i:s');
                            } else if ( $value instanceof Model\Identity ) {
                                $value = $value->getEndpoint();
                            }
                        } else if ( is_array( $value ) ) {
                            // TODO
                        }
                    } else if ( is_bool($value) ) {
                        $value = (int) $value;
                    }
                    return $value;
                }
                return null;
            }, $props )
        ];
    }

}
