<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_scopeConfig;
    protected $_helper;

    protected function _construct()
    {
        $this->_init(
            'Amasty\ShippingTableRates\Model\Rate',
            'Amasty\ShippingTableRates\Model\ResourceModel\Rate'
        );
    }

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function addMethodFilters($methodIds)
    {
        $this->addFieldToFilter('method_id', array('in' => $methodIds));

        return $this;
    }

    public function addAddressFilters($request)
    {
        $this->addFieldToFilter('country', array(
            array(
                'like' => $request->getDestCountryId(),
            ),
            array(
                'eq' => '0',
            ),
            array(
                'eq' => '',
            ),
        ));

        $this->addFieldToFilter('state', array(
            array(
                'like' => $request->getDestRegionId(),
            ),
            array(
                'eq' => '0',
            ),
            array(
                'eq' => '',
            ),
        ));

        $inputZip = $request->getDestPostcode();
        if ($this->_scopeConfig->getValue('carriers/amstrates/numeric_zip')) {
            if ($inputZip == '*') {
                $inputZip = '';
            }
            $zipData = $this->_helper->getDataFromZip($inputZip);
            $zipData['district'] = $zipData['district'] !== '' ? intval($zipData['district']) : -1;

            $this->getSelect()
                ->where('`num_zip_from` <= ? OR `zip_from` = ""', $zipData['district'])
                ->where('`num_zip_to` >= ? OR `zip_to` = ""', $zipData['district']);

            if (!empty($zipData['area'])) {
                $this->addFieldToFilter('zip_from', array(
                    array(array('regexp' => '^' . $zipData['area'] . '[0-9]+'), array('eq' => '')),
                ));
            }


            //to prefer rate with zip
            $this->setOrder('num_zip_from', 'DESC');
            $this->addOrder('num_zip_to', 'DESC');
        } else {
            $this->getSelect()->where("? LIKE zip_from OR zip_from = ''", $inputZip);
        }

        return $this;
    }

    public function addTotalsFilters($totals, $shippingType, $request, $allowFreePromo)
    {
        if (!($request->getFreeShipping() && $allowFreePromo)) {
            $this->addFieldToFilter('price_from', array('lteq' => $totals['not_free_price']));
            $this->addFieldToFilter('price_to', array('gteq' => $totals['not_free_price']));
        }
        $this->addFieldToFilter('weight_from', array('lteq' => $totals['not_free_weight']));
        $this->addFieldToFilter('weight_to', array('gteq' => $totals['not_free_weight']));
        $this->addFieldToFilter('qty_from', array('lteq' => $totals['not_free_qty']));
        $this->addFieldToFilter('qty_to', array('gteq' => $totals['not_free_qty']));
        $this->addFieldToFilter('shipping_type', array(
            array(
                'eq' => $shippingType,
            ),
            array(
                'eq' => '',
            ),
            array(
                'eq' => '0',
            ),
        ));
        return $this;
    }
}
