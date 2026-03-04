<?php

namespace Jtl\Connector\Vivino\Controller\Traits;

use Jtl\Connector\Core\Model as JTLModel;

trait Delete {


    /**
     * @param AbstractModel ...$models
     * @return AbstractModel[]
     * @throws \Psr\Log\InvalidArgumentException
     * @throws Exception
     */
    public function delete(JTLModel\AbstractModel ...$model) : array
    {
        $processedModels = [];
        // file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-fuckit-'.static::class.'::delete',var_export($model,true));

        foreach ( $model as $m ) {
            $processedModels[] = $this->deleteModel($m);
        }
        $this->em()->flush();
        return $processedModels;
    }
    abstract protected function deleteModel(JTLModel\AbstractModel $model );
}
