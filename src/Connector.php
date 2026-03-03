<?php

namespace Jtl\Connector\Vivino;

use DI\Container;
use Jtl\Connector\Core\Authentication\TokenValidatorInterface;
use Jtl\Connector\Core\Config\ConfigSchema;
use Jtl\Connector\Core\Connector\ConnectorInterface;
use Jtl\Connector\Core\Mapper\PrimaryKeyMapperInterface;
use Jtl\Connector\Core\Authentication\TokenValidator;
use Jtl\Connector\Vivino\Installer\Installer;
use Jtl\Connector\Vivino\Mapper\PrimaryKeyMapper;
use Noodlehaus\ConfigInterface;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Example Connector
 * @access public
 */
class Connector implements ConnectorInterface
{
    public const
        INSTALLER_LOCK_FILE = 'installer.lock';
    /**
     * @var ConfigInterface
     */
    protected $config;

    private static Connector $instance;

    /**
     * @var PDO
     */
    protected $pdo;

    protected EntityManager $em;

    public static function get() : Connector {
        if ( ! isset( static::$instance ) ) {

        }
        return static::$instance;
    }

    /**
     * @param ConfigInterface $config
     * @param Container $container
     * @param EventDispatcher $dispatcher
     */
    public function initialize(ConfigInterface $config, Container $container, EventDispatcher $dispatcher) : void
    {
        $connectorDir = $config->get(ConfigSchema::CONNECTOR_DIR);

        $this->config = $config;
        $this->pdo = $this->createPdoInstance($config->get('db'));

        // Doctrine\ORM
        $metadataConfig = Setup::createAnnotationMetadataConfiguration(
            [ $connectorDir."/src" ],
            ! in_array( getenv('APP_ENV'),['live','prod','production'] ),
            null, null, false
        );

        $this->em = EntityManager::create([ 'pdo' => $this->pdo ], $metadataConfig);

        $lockFile = sprintf('%s/%s', $connectorDir, self::INSTALLER_LOCK_FILE);
        if (!is_file($lockFile)) {
            $installer = new Installer($this->pdo, $connectorDir);
            $installer->run();
            file_put_contents($lockFile, sprintf('Created at %s', (new \DateTimeImmutable())->format('Y-m-d H:i:s')));
        }

        // Passing the instantiated database object to the DI container,
        // so it can be injected into the controllers by instantiation.
        // For more information about the di container see https://php-di.org/doc/
        $container->set(PDO::class, $this->pdo);
    }

    public function em() {
        return $this->em;
    }

    /**
     * Defining the primary key mapper which is used to manage the links between JTL-Wawi and the shop entities.
     *
     * @return PrimaryKeyMapperInterface
     */
    public function getPrimaryKeyMapper() : PrimaryKeyMapperInterface
    {
        return new PrimaryKeyMapper($this->pdo);
    }

    /**
     * Defining the token validator which is used to check the given token on an auth call.
     *
     * @return TokenValidatorInterface
     * @throws \Exception
     */
    public function getTokenValidator() : TokenValidatorInterface
    {
        return new TokenValidator($this->config->get("token"));
    }

    /**
     * Defining the controller namespace which holds the controller classes for all entities, so they can be found by the application.
     *
     * @return string
     */
    public function getControllerNamespace() : string
    {
        return "Jtl\Connector\Vivino\Controller";
    }

    /**
     * Defining the connectors version.
     *
     * @return string
     */
    public function getEndpointVersion() : string
    {
        return "0.1";
    }

    /**
     * Defining the connectors associated shop version. Should be empty for "Bulk" platform.
     *
     * @return string
     */
    public function getPlatformVersion() : string
    {
        return "";
    }

    /**
     * Defining the connectors associated shop name. Using "Bulk" as the default name for all third party connectors.
     *
     * @return string
     */
    public function getPlatformName() : string
    {
        return "Vivino";
    }

    /**
     * @param string[] $dbParams
     * @return PDO
     */
    private function createPdoInstance(array $dbParams) : PDO
    {
        $pdo = new PDO(
            sprintf("mysql:host=%s;dbname=%s", $dbParams["host"], $dbParams["name"]),
            $dbParams["username"],
            $dbParams["password"]
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
