<?php

namespace Jtl\Connector\Vivino\Controller\Traits;

use Jtl\Connector\Core\Model as JTLModel;

trait Push {
    public function push(JTLModel\AbstractModel ...$model) : array {

        $processedModels = [];
        file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-'.static::class.'::push',var_export($model,true));
        foreach ( $model as $m ) {
            $processedModels[] = $this->pushModel($m);
        }
        $this->em()->flush();
        return $processedModels;
    }
    abstract protected function pushModel(JTLModel\AbstractModel $model): JTLModel\AbstractModel;


}
