<?php

namespace Jtl\Connector\Vivino\Controller\Traits;

use Jtl\Connector\Core\Model;

trait SinglePull {


    /**
     * @param QueryFilter $query
     * @return AbstractIdentity[]|ProductModel[]
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function pull(Model\QueryFilter $query): array
    {
        $products = [];

        $stmt    = $this->pdo->prepare("SELECT * FROM products LIMIT ?;", [ $query->getLimit() ] );

        while ( $row = $stmt->fetch(PDO::FETCH_OBJ) ) {
            $productModel = $this->manager->parseDbData(new Model\Product(), $row);

            $products[] = $productModel;
        }

        return $products;

    }
}
