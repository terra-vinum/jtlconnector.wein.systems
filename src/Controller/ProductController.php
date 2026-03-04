<?php

declare(strict_types=1);

namespace Jtl\Connector\Vivino\Controller;


use DateTime;
use Exception;
use InvalidArgumentException;
use PDO;

use Jtl\Connector\Core\Controller\DeleteInterface;
use Jtl\Connector\Core\Controller\PullInterface;
use Jtl\Connector\Core\Controller\PushInterface;
use Jtl\Connector\Core\Controller\StatisticInterface;
use Jtl\Connector\Core\Exception\MustNotBeNullException;
use Jtl\Connector\Core\Exception\TranslatableAttributeException;

use Jtl\Connector\Core\Model as JTLModel;

use Jtl\Connector\Vivino;
use Jtl\Connector\Vivino\Models as LocalModel;
use PhpCsFixer\Utils;
use Jtl\Connector\Core\Utilities\Str;

class ProductController extends AbstractController implements PushInterface, DeleteInterface {
    use Traits\Delete;
    use Traits\Push;

    /*
    Werte für [dbo].[tArtikelShop].[nAktion]
    1: ProductController::push
    2:                          ProductPriceController::push
    3: ProductController::push, ProductPriceController::push
    4:                                                        ProductStockLevelController::push
    5: ProductController::push,                               ProductStockLevelController::push
    6:                          ProductPriceController::push, ProductStockLevelController::push
    7: ProductController::push, ProductPriceController::push, ProductStockLevelController::push
    */
    protected $modelCalss = LocalModel\Product::class;
    public const
        TYPE_PARENT = 'parent',
        TYPE_CHILD  = 'child',
        TYPE_SINGLE = 'single';

    protected function getLocalModel(JTLModel\AbstractModel $model,bool $create = true) : ?LocalModel\Product {

        $jtlId = $model->getId()->getHost();
        $repo  = $this->em()->getRepository(LocalModel\Product::class);

        if ( $localModel = $repo->findOneBy( ['jtlId' => $jtlId] ) ) {
            return $localModel;
        }
        if ( $create ) {
            $localModel = new LocalModel\Product();
            $localModel->setJtlId($jtlId);
            return $localModel;
        }
        return null;
    }

    /**
     * @param AbstractModel $model
     * @return AbstractModel[]
     */
    public function pushModel(JTLModel\AbstractModel $model): JTLModel\AbstractModel {

        // skip and remove inactive and parent products
        if ( ! $model->getIsActive() || static::TYPE_PARENT === $this->getProductType($model) ) {
            return $model;
        }

        $localModel = $this->getLocalModel($model);

        $ean = array_unshift(explode(',',$model->getEan()));

        $localModel
            ->setSku( $model->getSku() )
            ->setEan( $ean )
            ->setBottleSize( $model->getMeasurementQuantity() )
            ->setBottleQuantity( $model->getMinimumOrderQuantity() )
            ->setCountry( $model->getOriginCountry() )
            ->setPermitNegativeStock( $model->getPermitNegativeStock() ) // IF stock >= bottle_quantity OR permit_negative THEN yes
            ->setDeliveryTime( $model->getSupplierDeliveryTime() ) // IF delivery + handling < X THEN yes
            ->setHandlingTime( $model->getAdditionalHandlingTime() )
            ->setSupplierStock( $model->getSupplierStockLevel() )
            // ->set******( $model->getPackagingQuantity() )
            ;

        // TODO
        // packagingQuantity?
        foreach ($model->getI18ns() as $i18n) {
            $localModel->setProductName($i18n->getName());
            // TODO urlPath
        }

        foreach ($model->getAttributes() as $key => $attr) {
            foreach ($attr->getI18ns() as $i18n) {
                if ( $attrName = $this->getAttributeName( $i18n->getName() ) ) {
                    $attrName = Str::toPascalCase( $attrName );

                    $setter = "set{$attrName}";
                    if ( method_exists( $localModel, $setter ) ) {
                        $localModel->{$setter}($i18n->getValue());
                    }
                }
            }
        }

        if ( static::TYPE_CHILD === $this->getProductType($model) ) {
            foreach ( $model->getVariations() as $variation ) {
                foreach ( $variation->getI18ns() as $i18n ) {
                    if ( $wsProp = $this->getWSPropertyFromName($i18n->getName()) ) {
                        $propName = Str::toPascalCase( $wsProp->group_name );
                        $setter = "set{$propName}";
                        if ( method_exists( $localModel, $setter ) ) {
                            foreach ( $variation->getValues() as $value ) {
                                foreach ( $value->getI18ns() as $valueI18n ) {
                                    $localModel->{$setter}($valueI18n->getName());
                                }
                            }
                        }
                    }
                }
            }
        }
        // $this->pushPrice($model);

        foreach ( $model->getSpecialPrices() as $specialPrice ) {
            // TODO
        }

        foreach ( $model->getSpecifics() as $specific ) {
            if ( $wsProp = $this->getWSProperty($specific) ) {
                $propName = Str::toPascalCase( $wsProp->group_name );
                $setter = "set{$propName}";
                if ( method_exists( $localModel, $setter ) ) {
                    $localModel->{$setter}($wsProp->value_label);
                }
            }
        }

        $this->em()->persist( $localModel );

        return $model;
    }

    private function getWSProperty($specific) {
        $stmt = $this->pdo->prepare('SELECT * FROM properties WHERE value_id = ?');
        $stmt->execute([$specific->getSpecificValueId()->getHost()]);
        if ( $prop = $stmt->fetch(\PDO::FETCH_OBJ) ) {
            return $this->getWSPropertyFromName($prop->property_name);
        }
        return false;
    }

    private function getWSPropertyFromName($name) {

        $stmt = $this->pdo->prepare('SELECT * FROM weinsys_properties WHERE group_label = ?');
        $stmt->execute([$name]);
        $ws_prop = $stmt->fetch(\PDO::FETCH_OBJ);

        return $ws_prop;
    }

    private function getAttributeName( string $attrLabel ) {
        $attrNameStmt = $this->pdo->prepare('SELECT name FROM weinsys_attributes WHERE label = ?');
        $attrNameStmt->execute([$attrLabel]);
        return $attrNameStmt->fetch(\PDO::FETCH_COLUMN,0);
    }

    protected function deleteModel(JTLModel\AbstractModel $model ) {

        if ( $localModel = $this->getLocalModel($model,false)) {
            $this->em()->remove( $localModel );
        }

        return $model;
	}


    /**
     * @param ProductModel $product
     * @return string
     */
    protected function getProductType(JTLModel\Product $product): string {
        if ($product->getIsMasterProduct() === true) {
            return self::TYPE_PARENT;
        }
        if ($product->getMasterProductId()->getHost() > 0) {
            return self::TYPE_CHILD;
        }
        return self::TYPE_SINGLE;
    }


    public function pull(JTLModel\QueryFilter $queryFilter) : array {
        // file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-'.static::class.'::pull',var_export($model,true));
        $repo  = $this->em()->getRepository(LocalModel\Product::class);
        $result = [];
        foreach ( $repo->findAll() as $entity ) {
            $model = new JTLModels\Product( $entity->getId(), $entity->getJtlId() );
            $model->setStockLevel((float) $entity->getStock());
            $model->setSku( $entity->getSku() );
            $model->setEan( $entity->getEan() );
            $model->setMeasurementQuantity( $entity->getBottleSize() );
            $model->setMinimumOrderQuantity( $entity->getBottleQuantity() );
            $model->setOriginCountry( $entity->getCountry() );
            $model->setPermitNegativeStock( $entity->getPermitNegativeStock() );
            $model->setSupplierDeliveryTime( $entity->getDeliveryTime() );
            $model->setAdditionalHandlingTime( $entity->getHandlingTime() );
            $model->setSupplierStockLevel( $entity->getSupplierStock() );
            $result[] = $model;
        }

        return $result;
    }
}
