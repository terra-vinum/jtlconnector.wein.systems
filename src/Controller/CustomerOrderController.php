<?php

declare(strict_types=1);

namespace Jtl\Connector\Vivino\Controller;


use DateTime;
use DateTimeZone;
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
use Jtl\Connector\Vivino\Application as JTLApplication;
use Jtl\Connector\Vivino\Services\Options;
use Jtl\Connector\Vivino\Models as LocalModel;
use PhpCsFixer\Utils;
use Jtl\Connector\Core\Utilities\Str;

class CustomerOrderController extends AbstractController implements PullInterface {
    private $order_cols = [
        'order_id',
        'status',
        'order_type',
        'external_id',
        'currency_code',
        'tracking_number',
        'tracking_url',
        'items_shipping_sum',
        'items_tax_sum',
        'discount_sum',
        'items_total_sum',
        'authorized_at',
        'confirmation_sla_expires_at',
        'expected_shipping_date',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_street_1',
        'shipping_street_2',
        'shipping_zip',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'company',
        'shipped_at',
        'commission_rate',
        'dispatched_at',
        'delivered_at',
        'shipping_tax_percentage',
        'item_tax_percentage',
        'allows_vintage_changes',
        'express_shipping',
        'carrier_name',
        'service_name',
        'CompensationShippingSumTaxIncluded',
    ];
    private $item_cols = [
        'id',
        'item_name',
        'item_sku',
        'item_url',
        'item_bottle_quantity',
        'item_unit_count',
        'item_unit_price',
        'item_tax_amount',
        'item_total_amount',
        'item_tax_percentage',
    ];

    public function pull(JTLModel\QueryFilter $queryFilter) : array {
        // file_put_contents(getenv('JTL_ROOT_DIR').'/'.time().'-'.static::class.'::pull',var_export($queryFilter,true));

        $merchant_id = Options::get('vivino.merchant_id');
        $status_map = [
            'Confirmed' => JTLModel\CustomerOrder::STATUS_NEW,
            'Approved'  => JTLModel\CustomerOrder::STATUS_NEW,
            'Shipped'  => JTLModel\CustomerOrder::STATUS_SHIPPED,
        ];

        // STATUS_CANCELLED
        // STATUS_PARTIALLY_SHIPPED


        $jtlOrders = [];
        $orders = $this->getOrders($queryFilter->getLimit());
        foreach ( $orders as $order ) {

            // address
            $names = array_pad(
                explode( ' ', trim( $order->shipping_name ), 2 ),
                2,
                ''
            );
            $address = (new JTLModel\CustomerOrderShippingAddress())
                ->setId(new JTLModel\Identity("{$order->order_id}-addr"))
                ->setFirstName( (string) $names[0] )
                ->setLastName( (string) $names[1] )
                ->setEMail( (string) $order->shipping_email)
                ->setPhone( (string) $order->shipping_phone)
                ->setStreet( (string) $order->shipping_street_1)
                ->setExtraAddressLine( (string) $order->shipping_street_2)
                ->setZipCode( (string) $order->shipping_zip)
                ->setCity( (string) $order->shipping_city)
                ->setState( (string) $order->shipping_state)
                ->setCountryIso( (string) strtoupper($order->shipping_country));

            $positionsSum = 0;

            // order
            $jtlOrder = (new JTLModel\CustomerOrder())
                ->setId(new JTLModel\Identity($order->order_id))
                ->setCreationDate( $order->authorized_at )
                ->setCurrencyIso( $order->currency_code )
                // ->setNote( $order->get_customer_note() )
                ->setCustomerId( new JTLModel\Identity($order->shipping_email) )
                ->setOrderNumber( $order->order_id )
                ->setShippingMethodName( Options::get('vivino.shipping_method') )
                ->setEstimatedDeliveryDate(  $order->expected_shipping_date )
                // ->setPaymentModuleCode($this->util->mapPaymentModuleCode($order))
                ->setPaymentStatus(  Options::get('vivino.payment_status') )

                ->setStatus( $status_map[$order->status] )
                ->setShippingAddress( $address )
                ->setTotalSumGross( $order->items_total_sum );

            foreach ( $order->positions as $i => $position ) {
                $jtlPosition = (new JTLModel\CustomerOrderItem())
                    ->setId(new JTLModel\Identity("{$order->order_id}-item-{$i}"))
                    ->setType(JTLModel\CustomerOrderItem::TYPE_PRODUCT)
                    ->setName($position->item_name)
                    ->setSku($position->item_sku)
                    ->setVat($position->item_tax_percentage)
                    ->setQuantity($position->item_bottle_quantity * $position->item_unit_count)
                    ->setPrice($position->item_unit_price / $position->item_bottle_quantity)
                    ;
                $jtlOrder->addItem($jtlPosition);
                $positionsSum += $position->item_total_amount;
            }

            if ( $order->items_shipping_sum ) {
                $position = (new JTLModel\CustomerOrderItem())
                    ->setId(new JTLModel\Identity("{$order->order_id}-shipping"))
                    ->setType(JTLModel\CustomerOrderItem::TYPE_SHIPPING)
                    ->setName('Versandpauschale')
                    // ->setSku($position->item_sku)
                    ->setVat($order->shipping_tax_percentage)
                    ->setQuantity(1)
                    ->setPrice($order->items_shipping_sum)
                    ;
            }

            $discount = round($positionsSum + $order->items_shipping_sum * ( $order->shipping_tax_percentage ) - $positionsSum, 2);

            if ( $discount > 0 ) {
                $position = (new JTLModel\CustomerOrderItem())
                    ->setId(new JTLModel\Identity("{$order->order_id}-discount"))
                    ->setType(JTLModel\CustomerOrderItem::TYPE_COUPON)
                    ->setName('Vivino-Rabatt')
                    ->setVat($order->item_tax_percentage)
                    ->setQuantity(1)
                    ->setPriceGross(-$discount)
                    ;
            }

            $url = sprintf('https://merchants.vivino.com/merchants/%d/orders/%s',$merchant_id,$order->order_id);
            $jtlOrder->setAttributes(
                (new JTLModel\KeyValueAttribute())
                    ->setKey('vivino_order_url')
                    ->setValue(sprintf('https://merchants.vivino.com/merchants/%d/orders/%s',$merchant_id,$order->order_id))
            );
            $jtlOrders[] = $jtlOrder;
            JTLApplication::query("UPDATE vivino_order_positions SET is_processed = 1 WHERE order_id = ?",[$order->order_id])->execute();
        }


        return $jtlOrders;
    }

    private function getOrders(?int $query_limit) {
        if ( is_null($query_limit)) {
            $limit  = '';
        } else {
            $limit  = "LIMIT {$query_limit}";
        }

        $select = implode( ',', $this->order_cols );

        $states       = Options::get('vivino.import_order_states');
        $placeholders = implode(',',array_fill(0,count($states),'?'));

        $sql = "SELECT DISTINCT {$select}
            FROM vivino_order_positions
            WHERE NOT is_processed AND status IN ({$placeholders})
            GROUP BY order_id
            ORDER BY authorized_at DESC
            {$limit}";

        return array_map(
            [$this,'processVivinoOrder'],
            JTLApplication::query($sql,$states)->fetchAll(\PDO::FETCH_OBJ)
        );
    }

    private function processVivinoOrder(object $order) {

        $this->dateify($order,[
            'authorized_at',
            'expected_shipping_date',
            'shipped_at',
            'dispatched_at',
            'delivered_at',
        ]);

        $this->floatify($order,[
            'items_shipping_sum',
            'items_tax_sum',
            'discount_sum',
            'items_total_sum',
            'commission_rate',
            'shipping_tax_percentage',
            'CompensationShippingSumTaxIncluded',
        ]);

        // add items
        $select = implode( ',', $this->item_cols );
        $sql = "SELECT DISTINCT {$select}
            FROM vivino_order_positions
            WHERE order_id = ?";

        $order->positions = array_map(
            [ $this, 'processVivinoOrderPosition' ],
            JTLApplication::query($sql,[$order->order_id])->fetchAll(\PDO::FETCH_OBJ)
        );

        return $order;
    }

    private function processVivinoOrderPosition(object $position) {

        $this->floatify($position,[
            'item_unit_price',
            'item_tax_amount',
            'item_total_amount',
        ]);

        $this->intify($position,[
            'item_bottle_quantity',
            'item_unit_count',
        ]);
        return $position;
    }

    // TODO: move to helper class
    private function floatify( object $item, $props = [] ) {
        foreach ( $props as $prop ) {
            if ( ! is_null($item->{$prop}) ) {
                $item->{$prop} = floatval($item->{$prop});
            }
        }
    }

    private function intify( object $item, $props = [] ) {
        foreach ( $props as $prop ) {
            if ( ! is_null($item->{$prop}) ) {
                $item->{$prop} = intval($item->{$prop});
            }
        }
    }

    private function dateify( object $item, $props = [] ) {
        foreach ( $props as $prop ) {
            if ( ! is_null($item->{$prop}) ) {
                $item->{$prop} = DateTime::createFromFormat(
                    'Y-m-d H:i:s', $item->{$prop},
                     new DateTimeZone('UTC')
                );
            }
        }
    }

}
