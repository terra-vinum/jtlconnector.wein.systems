<?php

/**
 * @author    Jan Weskamp <jan.weskamp@jtl-software.com>
 * @copyright 2010-2013 JTL-Software GmbH
 */

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

use Jtl\Connector\Core\Model;
use Jtl\Connector\Vivino;

class ProductPriceController extends ProductController {

    public function pushModel(JTLModel\AbstractModel $model) : JTLModel\AbstractModel {

        if ( ! ( $localModel = $this->getLocalModel($model,false) ) ) {
            return $model;
        }

        foreach ( $model->getPrices() as $price ) {
            if ( $price->getCustomerGroupId()->getEndpoint() === 'VV' ) {
                foreach ( $price->getItems() as $priceItem ) {
                    $localModel->setBottlePrice(round($priceItem->getNetPrice() * (1 + $model->getVat() * 0.01),2)); //
                }
                break;
            }
        }
        $this->em()->persist( $localModel );
        return $model;
    }

}
