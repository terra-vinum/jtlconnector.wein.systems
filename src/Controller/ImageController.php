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

class ImageController extends AbstractController implements PushInterface {
    // use Traits\Delete;
    use Traits\Push;

    protected function getLocalModel(JTLModel\AbstractModel $model, bool $create = true) : ?LocalModel\Product {

        $jtlId = $model->getForeignKey()->getHost();
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
        if ( $localModel = $this->getLocalModel($model)) {
            // /media/image/product/{jtlId}/lg/{jtlId}.png
            $img = str_replace(
                [ '{jtlId}',               '{sku}' ],
                [ $localModel->getJtlId(), preg_replace('/[\._]/','-',$localModel->getSku()) ],
                getenv('SHOP_URL_IMAGE')
            );
            $localModel->setImage($img);
            $this->em()->persist( $localModel );
        }
        return $model;
    }

}
