<?php

namespace Jtl\Connector\Vivino\Controller;

use Jtl\Connector\Core\Controller\PullInterface;
use Jtl\Connector\Core\Controller\PushInterface;
use Jtl\Connector\Core\Model;
use Ramsey\Uuid\Uuid;

class GlobalDataController implements PullInterface, PushInterface
{
    /**
     * @inheritDoc
     */
    public function pull(Model\QueryFilter $queryFilter) : array
    {
        $result = [];
        
        $globalData = new Model\GlobalData;
        
        // ***************************************
        // * Static values for presentation only *
        // ***************************************
        
        // Languages
        $globalData->addLanguage(
            (new Model\Language())->setId(new Model\Identity('4faa508a23e3427889bfae0561d7915d'))
                ->setLanguageISO('de')
                ->setIsDefault(true)
                ->setNameGerman('Deutsch')
                ->setNameEnglish('German')
        );

        // Currencies
        $globalData->addCurrency(
            (new Model\Currency())->setId(new Model\Identity('56b0d7e12feb47838e2cd6c49f2cfd82'))
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
            (new Model\CustomerGroup())->setId(new Model\Identity('b1d7b4cbe4d846f0b323a9d840800177'))
                ->setIsDefault(false)
                ->setApplyNetPrice(true)
                ->addI18n((new Model\CustomerGroupI18n())->setName('Vivino')->setLanguageISO('de'))
        );
        
        // TaxRates
        $globalData->addTaxRate(
            (new Model\TaxRate())->setId(new Model\Identity('f1ec9220f3f64049926a83f5ba8df985'))
                ->setRate(19.0)
        );
        
        $globalData->addTaxRate(
            (new Model\TaxRate())->setId(new Model\Identity('ec0a029a85554745aa42fb708d3c5c8c'))
                ->setRate(7.0)
        );
        
        // shippingMethods
        $globalData->addShippingMethod(
            (new Model\ShippingMethod())->setId(new Model\Identity('7adeec3fbbe942c6a8e910ead168703d'))
                ->setName('DHL Versand')
        );
        
        $result[] = $globalData;
        
        return $result;
    }


    public function push(Model\AbstractModel $model): Model\AbstractModel
    {
        return $model;
    }
}
