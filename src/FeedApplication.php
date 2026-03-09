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
use Jtl\Connector\Core\Config\CoreConfigInterface;

class FeedApplication {

    private static $inited = false;
    private static FeedApplication $app;
    private static $appConfig;
    private static $appDir;
    private static $appPDO;

    public ConnectorInterface $connector;
    protected CoreConfigInterface $config;
    protected string              $connectorDir;

    public static function get() {
        if ( ! isset( static::$app ) ) {
            static::$app = new static(static::$appDir,static::$appConfig);
        }
        return static::$app;
    }

    public function __construct(
        string               $connectorDir,
        ?CoreConfigInterface $config       = null
    ) {
        $this->connectorDir = $connectorDir;
        $this->config       = $config;
        // TODO request
    }

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

    public function run(): void {
        // auth from request
        if ( ! isset($_GET['key']) || $_GET['key'] !== getenv('FEED_AUTH')) {
            http_response_code(403);
            echo "Forbidden";
            exit();
        }

        // @see https://vivino.slab.com/public/posts/9gq0o3dg

        $shop_host  = getenv('SHOP_URL_HOST');
        $image_path = getenv('SHOP_URL_IMAGE');
        // https://nl12c37a59.jtl-shop.de/media/image/product/12527/xs/9801000012112-4.png
        $stmt = static::pdo()->prepare("SELECT
                CONCAT(
                    IF(
                        vivino_name IS NOT NULL,
                        IF(
                            wine_vintage IS NOT NULL,
                            CONCAT( vivino_name,' ', wine_vintage ),
                            CONCAT( vivino_name,' ', 'NV' )
                        ),
                        product_name
                    ),
                    IF (
                        wine_color = 'Weiß',
                        ' White',
                        IF(
                        wine_color = 'Rosé',
                            ' Rosé',
                            ''
                        )
                    )
                ) AS 'product_name', -- product_name + Farbe wenn nicht rot
                ROUND(bottle_price * bottle_quantity,2) AS 'price', --
                CAST(bottle_size * 1000 AS int) AS 'bottle_size',
                bottle_quantity AS 'bottle_quantity',
                CONCAT('https://',:shophost,'/',link) AS 'link',
                CAST(COALESCE(stock,30) / bottle_quantity AS integer) AS 'inventory_count',
                sku AS 'product_id',
                JSON_OBJECT(
                    'image',                            CONCAT('https://', :shophost, '/',image),
                    'ean',                              gtin_vke,
                    'vintage',                          COALESCE(wine_vintage,'NV'),
                    'color',                            CASE
                                                            WHEN wine_color = 'Rot' THEN 'Red'
                                                            WHEN wine_color = 'Weiß' THEN 'White'
                                                            WHEN wine_color = 'Rosé' THEN 'Rosé'
                                                        END,
                    'country',                          country_names.country_name_en,
                    'ingredients',                      COALESCE(lbm_zutaten,''),
                    'alcohol',                          CONCAT(alkohol,'%'),
                    'residual_sugar',                   CAST(COALESCE(restzucker,0) AS DOUBLE(10,4)),
                    'energy',                           lbm_brennwert_kj,
                    'contains_milk_allergens',          IF ( COALESCE(allergen_milch,0), 'yes', 'no' ),
                    'contains_egg_allergens',           IF ( COALESCE(allergen_ei,0), 'yes', 'no' ),
                    'contains_gluten_allergens',        IF ( COALESCE(allergen_gluten,0), 'yes', 'no' ),
                    'contains_crustacean_allergens',    IF ( COALESCE(allergen_krebstier,0), 'yes', 'no' ),
                    'contains_fish_allergens',          IF ( COALESCE(allergen_fisch,0), 'yes', 'no' ),
                    'contains_peanut_allergens',        IF ( COALESCE(allergen_erdnuss,0), 'yes', 'no' ),
                    'contains_soybean_allergens',       IF ( COALESCE(allergen_soja,0), 'yes', 'no' ),
                    'contains_nut_allergens',           IF ( COALESCE(allergen_nuss,0), 'yes', 'no' ),
                    'contains_celery_allergens',        IF ( COALESCE(allergen_sellerie,0), 'yes', 'no' ),
                    'contains_mustard_allergens',       IF ( COALESCE(allergen_senf,0), 'yes', 'no' ),
                    'contains_sesame_seed',             IF ( COALESCE(allergen_sesam,0), 'yes', 'no' ),
                    'contains_lupin_allergens',         IF ( COALESCE(allergen_lupinen,0), 'yes', 'no' ),
                    'contains_mollusc_allergens',       IF ( COALESCE(allergen_weichtier,0), 'yes', 'no' )
                ) AS 'extras'
            FROM products
            LEFT JOIN country_names ON country_names.country_name_de = products.country
            -- TODO select by stock / status
            WHERE 1;" );
        $stmt->bindValue( ':shophost', getenv('SHOP_URL_HOST') );
        $stmt->execute();
        header('Content-Type: text/xml');
        echo '<vivino-product-list>';
        while ( $row = $stmt->fetch(\PDO::FETCH_OBJ) ) {
            $row->extras              = json_decode($row->extras);
            $row->product_name        = htmlspecialchars($row->product_name);
            $row->extras->ingredients = htmlspecialchars($row->extras->ingredients);
            $energy      = $row->extras->energy      ? "<energy>{$row->extras->energy}</energy>"                : '';
            $ingredients = $row->extras->ingredients ? "<ingredients>{$row->extras->ingredients}</ingredients>" : '';
            $alcohol     = $row->extras->alcohol     ? "<alcohol>{$row->extras->alcohol}</alcohol>"             : '';
            echo "<product>
<product-name>{$row->product_name}</product-name>
<price>{$row->price}</price>
<bottle_size>{$row->bottle_size} ml</bottle_size>
<bottle_quantity>{$row->bottle_quantity}</bottle_quantity>
<link>{$row->link}</link>
<inventory-count>{$row->inventory_count}</inventory-count>
<product-id>{$row->product_id}</product-id>
<extras>
<image>{$row->extras->image}</image>
<ean>{$row->extras->ean}</ean>
<vintage>{$row->extras->vintage}</vintage>
<color>{$row->extras->color}</color>
<country>{$row->extras->country}</country>
{$ingredients}
{$alcohol}
<residual-sugar unit=\"g/l\">{$row->extras->residual_sugar}</residual-sugar>
{$energy}
<contains-milk-allergens>{$row->extras->contains_milk_allergens}</contains-milk-allergens>
<contains-egg-allergens>{$row->extras->contains_egg_allergens}</contains-egg-allergens>
<contains-gluten-allergens>{$row->extras->contains_gluten_allergens}</contains-gluten-allergens>
<contains-crustacean-allergens>{$row->extras->contains_crustacean_allergens}</contains-crustacean-allergens>
<contains-fish-allergens>{$row->extras->contains_fish_allergens}</contains-fish-allergens>
<contains-peanut-allergens>{$row->extras->contains_peanut_allergens}</contains-peanut-allergens>
<contains-soybean-allergens>{$row->extras->contains_soybean_allergens}</contains-soybean-allergens>
<contains-nut-allergens>{$row->extras->contains_nut_allergens}</contains-nut-allergens>
<contains-celery-allergens>{$row->extras->contains_celery_allergens}</contains-celery-allergens>
<contains-mustard-allergens>{$row->extras->contains_mustard_allergens}</contains-mustard-allergens>
<contains-sesame-seed-allergens>{$row->extras->contains_sesame_seed}</contains-sesame-seed-allergens>
<contains-lupin-allergens>{$row->extras->contains_lupin_allergens}</contains-lupin-allergens>
<contains-mollusc-allergens>{$row->extras->contains_mollusc_allergens}</contains-mollusc-allergens>
</extras>
</product>";
        }
        echo '</vivino-product-list>';
    }

/*

allergen_sulfite




allergen_albumin

allergen_kasein


allergen_lysozym






allergen_farbstoffe
allergen_aromen
allergen_konservierungsstoffe
allergen_antioxidanzien


<vivino-product-list>
	<product>
		<product-name>Bidoli Sauvignon Blanc 2024 White</product-name>
		<price>59.34</price>
		<bottle_size>750 ml</bottle_size>
		<bottle_quantity>6</bottle_quantity>
		<link>https://www.terra-vinum.de/4/Bidoli-Sauvignon-Blanc?number=9512000080073.5</link>
		<inventory-count>5</inventory-count>
		<product-id>50-9512000080073.5</product-id>
		<extras>
			<image>https://www.terra-vinum.de/media/image/ac/26/15/80073-121-2022_1280x1280.png</image>
			<ean>8032732054114</ean>
			<vintage>2024</vintage>
			<color>White</color>
			<country>Italia</country>
			<ingredients>Trauben</ingredients>
			<alcohol>12.5%</alcohol>
			<residual-sugar unit="g/l">0.2</residual-sugar>
			<energy>293</energy>
			<contains-milk-allergens>no</contains-milk-allergens>
			<contains-egg-allergens>no</contains-egg-allergens>
			<contains-gluten-allergens>no</contains-gluten-allergens>
			<contains-crustacean-allergens>no</contains-crustacean-allergens>
			<contains-fish-allergens>no</contains-fish-allergens>
			<contains-peanut-allergens>no</contains-peanut-allergens>
			<contains-soybean-allergens>no</contains-soybean-allergens>
			<contains-nut-allergens>no</contains-nut-allergens>
			<contains-celery-allergens>no</contains-celery-allergens>
			<contains-mustard-allergens>no</contains-mustard-allergens>
			<contains-sesame-seed-allergens>no</contains-sesame-seed-allergens>
			<contains-lupin-allergens>no</contains-lupin-allergens>
			<contains-mollusc-allergens>no</contains-mollusc-allergens>
        </extras>
	</product>
    ...
</vivino-product-list>
*/

}
