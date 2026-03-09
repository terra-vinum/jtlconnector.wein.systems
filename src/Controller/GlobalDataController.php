<?php

namespace Jtl\Connector\Vivino\Controller;

use Jtl\Connector\Core\Controller\PullInterface;
use Jtl\Connector\Core\Controller\PushInterface;
use Jtl\Connector\Core\Model as JTLModel;
use Ramsey\Uuid\Uuid;

class GlobalDataController extends AbstractController implements PullInterface, PushInterface {

    // use Traits\Push;

    /**
     * @inheritDoc
     */
    public function pull(JTLModel\QueryFilter $queryFilter) : array
    {
        $result = [];

        $globalData = new JTLModel\GlobalData();

        // ***************************************
        // * Static values for presentation only *
        // ***************************************

        // Languages
        $globalData->addLanguage(
            (new JTLModel\Language())->setId(new JTLModel\Identity('4faa508a23e3427889bfae0561d7915d'))
                ->setLanguageISO('de')
                ->setIsDefault(true)
                ->setNameGerman('Deutsch')
                ->setNameEnglish('German')
        );

        // Currencies
        $globalData->addCurrency(
            (new JTLModel\Currency())->setId(new JTLModel\Identity('56b0d7e12feb47838e2cd6c49f2cfd82'))
                ->setIsDefault(true)
                ->setName('Euro')
                ->setDelimiterCent(',')
                ->setDelimiterThousand('.')
                ->setFactor(1.0)
                ->setHasCurrencySignBeforeValue(false)
                ->setIso('EUR')
                ->setNameHtml('&euro;')
        );

        // CustomerGroups

        $globalData->addCustomerGroup(
            (new JTLModel\CustomerGroup())->setId(new JTLModel\Identity('VV'))
                ->setIsDefault(true)
                ->setApplyNetPrice(false)
                ->addI18n((new JTLModel\CustomerGroupI18n())->setName('Vivino')->setLanguageISO('de'))
        );
        $globalData->addCustomerGroup(
            (new JTLModel\CustomerGroup())->setId(new JTLModel\Identity('EK'))
                ->setIsDefault(false)
                ->setApplyNetPrice(false)
                ->addI18n((new JTLModel\CustomerGroupI18n())->setName('Terra-Vinum')->setLanguageISO('de'))
        );

        // TaxRates
        $globalData->addTaxRate(
            (new JTLModel\TaxRate())->setId(new JTLModel\Identity('f1ec9220f3f64049926a83f5ba8df985'))
                ->setRate(19.0)
        );

        $globalData->addTaxRate(
            (new JTLModel\TaxRate())->setId(new JTLModel\Identity('ec0a029a85554745aa42fb708d3c5c8c'))
                ->setRate(7.0)
        );

        // shippingMethods
        $globalData->addShippingMethod(
            (new JTLModel\ShippingMethod())->setId(new JTLModel\Identity('7adeec3fbbe942c6a8e910ead168703d'))
                ->setName('DHL Versand')
        );

        $result[] = $globalData;

        return $result;
    }

}
