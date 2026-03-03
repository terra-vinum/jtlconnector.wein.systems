<?php

namespace Jtl\Connector\Vivino\Controller;

use PDO;
use Datetime;

use Doctrine\Common\Annotations;

use Jtl\Connector\Core\Logger\LoggerService;
use Jtl\Connector\Core\Model as JTLModel;
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

use Doctrine\ORM\EntityManager;

use Jtl\Connector\Vivino\Models as LocalModel;
use Jtl\Connector\Vivino\Application;

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
    protected MonoLogger $loggerService;

	/**
	 * Using direct dependencies for better testing and easier use with a DI container.
	 *
	 * AbstractController constructor.
	 * @param PDO $pdo
	 */
	public function __construct(PDO $pdo)
	{
        global $connector;

		$this->pdo = $pdo;
	}

    protected function em() {
        return Application::get()->em();
    }

    /**
     * @param AbstractModel ...$models
     * @return AbstractModel[]
     * @throws \Psr\Log\InvalidArgumentException
     * @throws Exception
     */
    public function delete(JTLModel\AbstractModel ...$model) : array
    {
        $processedModels = [];
        // file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-fuckit-'.static::class,var_export($model,true));

        foreach ( $model as $m ) {
            $processedModels[] = $this->deleteModel($m);
        }
        $this->em()->flush();
        return $processedModels;
    }

    public function push(JTLModel\AbstractModel ...$model) : array {

        $processedModels = [];
        // file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-'.static::class,var_export($model,true));
        foreach ( $model as $m ) {
            $processedModels[] = $this->pushModel($m);
        }
        $this->em()->flush();
        return $processedModels;
    }

    abstract protected function pushModel(JTLModel\AbstractModel $model): JTLModel\AbstractModel;

    abstract protected function deleteModel(JTLModel\AbstractModel $model );



    protected function fetchModel($modelClass, string $table, $id ) {

    }

}
