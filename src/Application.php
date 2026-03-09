<?php

namespace Jtl\Connector\Vivino;

use PDO;
use Jtl\Connector\Core\Application\Application as JTLApplication;
use Symfony\Component\Dotenv\Dotenv;
use Jtl\Connector\Core\Config\ConfigParameter;
use Jtl\Connector\Core\Config\ConfigSchema;
use Jtl\Connector\Core\Config\ArrayConfig;
use Jtl\Connector\Core\Connector\ConnectorInterface;
use Doctrine\ORM\EntityManager;

class Application extends JTLApplication {

    private static $inited = false;
    private static Application $app;
    private static $appConfig;
    private static $appDir;
    private static $appPDO;

    public ConnectorInterface $connector;

    public static function init($appDir) {
        if ( static::$inited ) {
            return;
        }
        $dotenv = new Dotenv();
        $dotenv->usePutenv()->loadEnv($appDir.'/.env');

        \Sentry\init([
            'dsn'         => getenv('SENTRY_DSN'),
            'environment' => getenv('APP_ENV'),
        ]);
        static::$appDir = $appDir;
        static::$appConfig = new ArrayConfig([
            'db' => [
                'host'     => getenv('DB_HOST'),
                'name'     => getenv('DB_NAME'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
            ],
            'token' => getenv('APP_TOKEN'),
        ]);

        static::$inited = true;
    }

    public static function pdo() {
        if ( ! isset( static::$appPDO ) ) {
            $dbParams = static::$appConfig->get('db');
            static::$appPDO = new PDO(
                sprintf("mysql:host=%s;dbname=%s", $dbParams["host"], $dbParams["name"]),
                $dbParams["username"],
                $dbParams["password"]
            );
        }
        return static::$appPDO;
    }


    public static function query($sql,$params) {
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function get() {
        if ( ! isset( static::$app ) ) {
            static::$app =new static(static::$appDir,static::$appConfig);
        }
        return static::$app;
    }

    public function run(ConnectorInterface $connector): void {
        $this->connector = $connector;
        parent::run($connector);
    }

    public function em() : EntityManager {
        return $this->connector->em();
    }

}
