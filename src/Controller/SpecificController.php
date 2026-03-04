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

class SpecificController extends AbstractController implements PushInterface, DeleteInterface {

    use Traits\Delete;
    use Traits\Push;

    public function pushModel(JTLModel\AbstractModel $model): JTLModel\AbstractModel {

        $stmt = $this->pdo->prepare("INSERT INTO properties (`property_name`,`property_id`,`value_name`,`value_id`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE property_name = ?, value_name = ?;");
        $property_id = $model->getId()->getHost();
        foreach ($model->getI18ns() as $i18n) {
            $property_name = $i18n->getName();
        }

        foreach ( $model->getValues() as $value ) {
            $value_id = $value->getId()->getHost();
            foreach ($value->getI18ns() as $i18n) {
                $value_name = $i18n->getValue();
            }

            $stmt->execute( [ $property_name, $property_id, $value_name, $value_id, $property_name, $value_name ] );
        }

        return $model;
    }

    public function deleteModel(JTLModel\AbstractModel $model): JTLModel\AbstractModel {
        return $model;
    }

}
