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

use Jtl\Connector\Core\Model;
// use Jtl\Connector\Core\Model\AbstractIdentity;
// use Jtl\Connector\Core\Model\AbstractModel;
// use Jtl\Connector\Core\Model\Identity;
// use Jtl\Connector\Core\Model\Product;
// use Jtl\Connector\Core\Model\Product as ProductModel;
// use Jtl\Connector\Core\Model\ProductAttribute;
// use Jtl\Connector\Core\Model\ProductI18n as ProductI18nModel;
// use Jtl\Connector\Core\Model\ProductSpecific;
// use Jtl\Connector\Core\Model\ProductVariation;
// use Jtl\Connector\Core\Model\QueryFilter;
// use Jtl\Connector\Core\Model\TaxRate;

use Jtl\Connector\Vivino;

// use JtlWooCommerceConnector\Controllers\Product\Product2CategoryController;
// use JtlWooCommerceConnector\Controllers\Product\ProductAdvancedCustomFieldsController;
// use JtlWooCommerceConnector\Controllers\Product\ProductB2BMarketFieldsController;
// use JtlWooCommerceConnector\Controllers\Product\ProductDeliveryTimeController;
// use JtlWooCommerceConnector\Controllers\Product\ProductGermanizedFieldsController;
// use JtlWooCommerceConnector\Controllers\Product\ProductGermanMarketFieldsController;
// use JtlWooCommerceConnector\Controllers\Product\ProductI18nController;
// use JtlWooCommerceConnector\Controllers\Product\ProductManufacturerController;
// use JtlWooCommerceConnector\Controllers\Product\ProductMetaSeoController;
// use JtlWooCommerceConnector\Controllers\Product\ProductPrice;
// use JtlWooCommerceConnector\Controllers\Product\ProductSpecialPriceController;
// use JtlWooCommerceConnector\Controllers\Product\ProductVaSpeAttrHandlerController;
// use JtlWooCommerceConnector\Integrations\Plugins\Wpml\WpmlProduct;
// use JtlWooCommerceConnector\Logger\ErrorFormatter;
// use JtlWooCommerceConnector\Traits\WawiProductPriceSchmuddelTrait;
// use JtlWooCommerceConnector\Utilities\Config;
// use JtlWooCommerceConnector\Utilities\SqlHelper;
// use JtlWooCommerceConnector\Utilities\SupportedPlugins;
// use JtlWooCommerceConnector\Utilities\Util;
// use PhpUnitsOfMeasure\Exception\NonNumericValue;
// use PhpUnitsOfMeasure\Exception\NonStringUnitName;
// use WC_Data_Exception;
// use WC_Product;

class ProductController extends AbstractController implements
    PullInterface,
    PushInterface,
    DeleteInterface,
    StatisticInterface
{

    use Traits\SinglePull;

    public const
        TYPE_PARENT = 'parent',
        TYPE_CHILD  = 'child',
        TYPE_SINGLE = 'single';


    protected string $modelClass = 'Jtl\Connector\Core\Model\Product';
    protected string $table      = 'products';
    protected array  $id_columns = ['id'];
    protected array  $columns    = [
        'id',
        'creation_date',
        'modified',
        'is_master_product',
        'master_product_id',
        'product_type_id',
        'shipping_class_id',
        'sku',
        'ean',
        'is_active',
        'tax_class_id',
        'minimum_order_quantity',
        'minimum_quantity',
        'vat',
        'recommended_retail_price',
        'packaging_quantity',
        'base_price_unit_id',
        'base_price_divisor',
        'base_price_factor',
        'base_price_quantity',
        'base_price_unit_code',
        'base_price_unit_name',
        'additional_handling_time',
        'available_from',
        'consider_base_price',
        'discountable',
        'consider_stock',
        'consider_variation_stock',
        'permit_negative_stock',
        'stock_level',
        'supplier_delivery_time',
        'supplier_stock_level',
    ];


    /**
     * @param AbstractModel ...$models
     * @phpstan-param Product ...$model
     *
     * @return AbstractModel[]
     * @throws InvalidArgumentException
     * @throws MustNotBeNullException
     * @throws NonNumericValue
     * @throws NonStringUnitName
     * @throws TranslatableAttributeException
     * @throws WC_Data_Exception
     * @throws \DateInvalidTimeZoneException
     * @throws \DateMalformedStringException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \TypeError
     * @throws \WP_Exception
     */
    public function push(Model\AbstractModel $model): Model\AbstractModel
    {
        $values = $this->getModelValues( $model, $this->columns );
        $this->persistValues(
            $values,
            $this->table, 
            $this->columns, 
            $this->id_columns
        );

        foreach ($model->getI18ns() as $i18n) {

            $values = [
                'model_id' => $model->getId()->getEndpoint(),
            ] + $this->getModelValues($i18n,[
                'language_iso',
                'delivery_status',
                'description',
                'measurement_unit_name',
                'meta_description',
                'meta_keywords',
                'name',
                'short_description',
                'title_tag',
                'unit_name',
                'url_path',
            ]);

            $this->persistValues(
                $values,
                'product_translations', 
                [
                    'model_id',
                    'language_iso',
                    'delivery_status',
                    'description',
                    'measurement_unit_name',
                    'meta_description',
                    'meta_keywords',
                    'name',
                    'short_description',
                    'title_tag',
                    'unit_name',
                    'url_path',
                ], 
                [
                    'model_id',
                    'language_iso',
                ]
            );
        }

        foreach ($model->getAttributes() as $attr) {
            $values = [
                'product_id' => $model->getId()->getEndpoint(),
            ] + $this->getModelValues( $attr, [
                'id',
                'is_translated',
                'is_custom_property',
                'type',
            ] );
            $this->persistValues(
                $values,
                'product_attrs', 
                [
                    'is_translated',
                    'is_custom_property',
                    'type',
                ], 
                [ 'id' ]
            );
            foreach ($attr->getI18ns() as $attrI18n) {

                $values = [
                    'model_id' => $attr->getId()->getEndpoint(),
                ] + $this->getModelValues($attrI18n,[
                    'language_iso',
                    'name',
                    'value',
                ]);
// throw new \Exception(var_export($values,true));
                $this->persistValues(
                    $values,
                    'product_attr_translations', 
                    [
                        'model_id',
                        'language_iso',
                        'name',
                        'value',
                    ], 
                    [ 'model_id', 'language_iso' ]
                );
            }
        }

        foreach ( $model->getPrices() as $price ) {
            $values = [
                'product_id' => $model->getId()->getEndpoint(),
            ] + $this->getModelValues( $price, [
                'id',
                'customer_group_id',
            ] );
            $this->persistValues(
                $values,
                'product_prices', 
                [
                    'id',
                    'product_id',
                    'customer_group_id',
                ], 
                [ 'id' ]
            );
            foreach ( $price->getItems() as $priceItem ) {
                $values = [
                    'price_id' => $price->getId()->getEndpoint(),
                ] + $this->getModelValues( $priceItem, [
                    'id',
                    'net_price',
                    'quantity',
                ] );
                $this->persistValues(
                    $values,
                    'product_price_items', 
                    [
                        'id',
                        'price_id',
                        'net_price',
                        'quantity',
                    ], 
                    [ 'id', 'price_id' ]
                );
            }
        }

        foreach ( $model->getSpecialPrices() as $specialPrice ) {
            $values = [
                'product_id' => $model->getId()->getEndpoint(),
            ] + $this->getModelValues( $specialPrice, [
                'id',
                'active_from_date',
                'active_until_date',
                'consider_date_limit',
                'consider_stock_limit',
                'is_active',
                'stock_limit',
            ] );
            $this->persistValues(
                $values,
                'product_special_prices', 
                [
                    'id',
                    'product_id',
                    'active_from_date',
                    'active_until_date',
                    'consider_date_limit',
                    'consider_stock_limit',
                    'is_active',
                    'stock_limit',
                ], 
                [ 'id' ]
            );
            foreach ( $specialPrice->getItems() as $specialPriceItem ) {
                $values = [
                    'product_special_price_id' => $specialPrice->getId()->getEndpoint(),
                ] + $this->getModelValues( $specialPriceItem, [
                    'id',
                    'customer_group_id',
                    'price_net',
                ] );
            }
        }

        return $model;
    }

    /**
     * @param AbstractModel ...$models
     * @return AbstractModel[]
     * @throws \Psr\Log\InvalidArgumentException
     * @throws Exception
     */
    public function delete(Model\AbstractModel ...$models): Model\AbstractModel
    {
        $returnModels = [];

        foreach ($models as $model) {
            /** @var Product $model */
            $productId = (int)$model->getId()->getEndpoint();

            $wcProduct = \wc_get_product($productId);

            if ($wcProduct instanceof \WC_Product) {
                \wp_delete_post($productId, true);
                \wc_delete_product_transients($productId);

                if ($this->wpml->canBeUsed()) {
                    /** @var WpmlProduct $wpmlProduct */
                    $wpmlProduct = $this->wpml->getComponent(WpmlProduct::class);
                    $wpmlProduct->deleteTranslations($wcProduct);
                }

                unset(self::$idCache[$model->getId()->getHost()]);
            }

            $returnModels[] = $model;
        }

        return $returnModels;
    }

    /**
     * @param QueryFilter $query
     * @return int
     * @throws \Psr\Log\InvalidArgumentException
     * @throws Exception
     */
    public function statistic(Model\QueryFilter $query): int
    {
        if ($this->wpml->canBeUsed()) {
            /** @var WpmlProduct $wpmlProduct */
            $wpmlProduct = $this->wpml->getComponent(WpmlProduct::class);
            $ids         = $wpmlProduct->getProducts();
        } else {
            $ids = $this->db->queryList(SqlHelper::productPull());
        }
        return \count($ids);
    }

    /**
     * @param ProductModel     $product
     * @param ProductI18nModel $meta
     * @return void
     * @throws InvalidArgumentException
     * @throws TranslatableAttributeException
     * @throws WC_Data_Exception
     * @throws MustNotBeNullException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \TypeError
     * @throws Exception
     */
    protected function onProductInserted(Model\ProductModel &$product, Model\ProductI18nModel &$meta): void
    {
        $wcProduct   = \wc_get_product($product->getId()->getEndpoint());
        $productType = $this->getType($product);

        if (\is_null($wcProduct) || $wcProduct === false) {
            return;
        }

        $this->updateProductMeta($product, $wcProduct);
        $this->updateProductRelations($product, $wcProduct, $productType);

        (new ProductVaSpeAttrHandlerController($this->db, $this->util))->pushDataNew($product, $wcProduct, $meta);

        if ($productType !== ProductController::TYPE_CHILD) {
            $this->updateProduct($product, $wcProduct);
            \wc_delete_product_transients((int)$product->getId()->getEndpoint());
        }

        //variations
        if ($productType === ProductController::TYPE_CHILD) {
            $this->updateVariationCombinationChild($product, $wcProduct, $meta);
        }

        $this->updateProductType($product, $wcProduct);
    }

    /**
     * @param ProductModel $jtlProduct
     * @param WC_Product   $wcProduct
     * @return void
     * @throws TranslatableAttributeException
     * @throws InvalidArgumentException
     */
    public function updateProductType(Model\ProductModel $jtlProduct, WC_Product $wcProduct): void
    {
        $productId            = $wcProduct->get_id();
        $customProductTypeSet = false;

        foreach ($jtlProduct->getAttributes() as $key => $pushedAttribute) {
            foreach ($pushedAttribute->getI18ns() as $i18n) {
                if (!$this->util->isWooCommerceLanguage($i18n->getLanguageISO())) {
                    continue;
                }

                $attrName = \strtolower(\trim($i18n->getName()));

                if (\strcmp($attrName, ProductVaSpeAttrHandlerController::PRODUCT_TYPE_ATTR) === 0) {
                    /** @var string $value */
                    $value = $i18n->getValue();

                    $allowedTypes = \wc_get_product_types();

                    if (\in_array($value, \array_keys($allowedTypes))) {
                        $term = \get_term_by('slug', \wc_sanitize_taxonomy_name(
                            $value
                        ), 'product_type');

                        if ($term instanceof \WP_Term) {
                            $productTypeTerms = \wc_get_object_terms($productId, 'product_type');
                            if (\is_array($productTypeTerms) && \count($productTypeTerms) === 1) {
                                $oldProductTypeTerm = \end($productTypeTerms);
                                if ($oldProductTypeTerm->term_id !== $term->term_id) {
                                    $removeObjTermsResult = \wp_remove_object_terms(
                                        $productId,
                                        [$oldProductTypeTerm->term_id],
                                        'product_type'
                                    );
                                    if ($removeObjTermsResult === true) {
                                        $result = \wp_add_object_terms(
                                            $productId,
                                            [$term->term_id],
                                            'product_type'
                                        );
                                        if (($result instanceof \WP_Error === false) && \is_array($result)) {
                                            $customProductTypeSet = true;
                                        }
                                    }
                                } else {
                                    $customProductTypeSet = true;
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }

        if ($customProductTypeSet === false) {
            $oldWcProductType = $this->getWcProductType($jtlProduct);

            $productTypeTerm    = \get_term_by('slug', $oldWcProductType, 'product_type');
            $currentProductType = \wp_get_object_terms($wcProduct->get_id(), 'product_type');

            if ($currentProductType instanceof \WP_Error) {
                throw new InvalidArgumentException(
                    "Expected current product type to be iterable. Got WP_Error."
                );
            }

            $removeTerm = null;
            foreach ($currentProductType as $term) {
                if ($term instanceof \WP_Term) {
                    $removeTerm = $term->term_id;
                }
            }

            if (\is_int($removeTerm)) {
                \wp_remove_object_terms($wcProduct->get_id(), $removeTerm, 'product_type');
            }

            if ($productTypeTerm instanceof \WP_Term) {
                \wp_set_object_terms($wcProduct->get_id(), $productTypeTerm->term_id, 'product_type', false);
            } else {
                \wp_set_object_terms($wcProduct->get_id(), $oldWcProductType, 'product_type', false);
            }
        }
    }


    /**
     * @param ProductModel     $product
     * @param WC_Product       $wcProduct
     * @param ProductI18nModel $meta
     * @return void
     * @throws Exception
     */
    public function updateVariationCombinationChild(
        Model\ProductModel $product,
        Model\ProductI18nModel $meta
    ): void {
        $productId = (int)$wcProduct->get_id();

        $productTitle         = \esc_html(\get_the_title((int)$product->getMasterProductId()->getEndpoint()));
        $variation_post_title = \sprintf(\__('Variation #%s of %s', 'woocommerce'), $productId, $productTitle);
        \wp_update_post([
            'ID' => $productId,
            'post_title' => $variation_post_title,
        ]);
        \update_post_meta($productId, '_variation_description', $meta->getDescription());
        \update_post_meta($productId, '_mini_dec', $meta->getShortDescription());

        (new \JtlWooCommerceConnector\Controllers\Product\ProductStockLevelController($this->db, $this->util))
            ->pushDataChild($product);
    }

}
