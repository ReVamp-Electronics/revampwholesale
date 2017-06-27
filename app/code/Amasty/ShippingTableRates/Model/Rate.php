<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Magento\Framework\Model\AbstractModel;

class Rate extends AbstractModel
{
    protected $_helper;
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_shippingTypes = [];
    protected $_existingShippingTypes = [];
    protected $_file;
    protected $_data = [];

    const MAX_LINE_LENGTH = 50000;
    const COL_NUMS = 18;
    const HIDDEN_COLUMNS = 2;
    const BATCH_SIZE = 50000;
    const COUNTRY = 0;
    const STATE = 1;
    const ZIP_FROM = 2;
    const NUM_ZIP_FROM = 17;
    const ZIP_TO = 3;
    const NUM_ZIP_TO = 18;
    const PRICE_TO = 5;
    const WEIGHT_TO = 7;
    const QTY_TO = 9;
    const SHIPPING_TYPE = 10;
    const ALGORITHM_SUM = 0;
    const ALGORITHM_MAX = 1;
    const ALGORITHM_MIN = 2;
    const MAX_VALUE = 99999999;

    protected function _construct()
    {
        $this->_init('Amasty\ShippingTableRates\Model\ResourceModel\Rate');
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Model\Context $context
    )
    {
        $this->_file = $file;
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $coreRegistry);
    }

    public function deleteBy($methodId)
    {
        /**
         * @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate $resource
         */
        $resource = $this->_objectManager->get('Amasty\ShippingTableRates\Model\ResourceModel\Rate');
        $resource->deleteBy($methodId);
    }

    public function import($methodId, $fileName)
    {
        $err = [];

        $fp = $this->_file->fileOpen($fileName, 'r');
        $methodId = intval($methodId);
        if (!$methodId) {
            $err[] = __('Specify a valid method ID.');
            return $err;
        }

        $countryCodes = $this->_helper->getCountries();
        $countryNames = $this->_helper->getCountries(true);
        $typeLabels = $this->_helper->getTypes(true);

        $data = [];

        $currLineNum = 0;
        while (($line = $this->_file->fileGetCsv($fp, self::MAX_LINE_LENGTH, ',', '"')) !== false) {
            $currLineNum++;

            if ($currLineNum == 1) {
                continue;
            }

            if ((count($line) + self::HIDDEN_COLUMNS) != self::COL_NUMS) {
                $err[] = 'Line #' . $currLineNum . ': warning, expected number of columns is ' . self::COL_NUMS;
                if (count($line) > self::COL_NUMS) {
                    for ($i = 0; $i < count($line) - self::COL_NUMS; $i++) {
                        unset($line[self::COL_NUMS + $i]);
                    }
                }

                if (count($line) < self::COL_NUMS) {
                    for ($i = 0; $i < self::COL_NUMS - count($line); $i++) {
                        $line[count($line) + $i] = 0;
                    }
                }
            }

            $dataZipFrom = $this->_helper->getDataFromZip($line[self::ZIP_FROM]);
            $dataZipTo = $this->_helper->getDataFromZip($line[self::ZIP_TO]);
            $line[self::NUM_ZIP_FROM] = $dataZipFrom['district'];
            $line[self::NUM_ZIP_TO] = $dataZipTo['district'];

            for ($i = 0; $i < self::COL_NUMS - self::HIDDEN_COLUMNS; $i++) {
                $line[$i] = str_replace(["\r", "\n", "\t", "\\", '"', "'", "*"], '', $line[$i]);
            }

            $countries = [''];
            if ($line[self::COUNTRY]) {
                $countries = explode(',', $line[self::COUNTRY]);
            } else {
                $line[self::COUNTRY] = '0';
            }

            $line = $this->_setDefaultLineValues($line);

            $typesData = $this->_prepareLineTypes($line, $err, $currLineNum, $typeLabels);
            $line = $typesData['line'];

            foreach ($countries as $country) {
                if ($country == 'All') {
                    $country = 0;
                }

                if ($country && empty($countryCodes[$country])) {
                    if (in_array($country, $countryNames)) {
                        $countryCodes[$country] = array_search($country, $countryNames);
                    } else {
                        $err[] = 'Line #' . $currLineNum . ': invalid country code ' . $country;
                        continue;
                    }

                }
                $line[self::COUNTRY] = $country ? $countryCodes[$country] : '0';

                $statesData = $this->_prepareLineStates($line, $err, $currLineNum, $country, $methodId);
            }// countries
        } // end while read
        fclose($fp);

        if (isset($statesData['data_index'])) {
            $err = $this->returnErrors($statesData['data'], $methodId, $currLineNum, $statesData['err']);
        }

        return $err;
    }

    public function returnErrors($data, $methodId, $currLineNum, $err)
    {
        /**
         * @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate $resource
         */
        $resource = $this->_objectManager->get('Amasty\ShippingTableRates\Model\ResourceModel\Rate');
        $errText = $resource->batchInsert($methodId, $data);

        if ($errText) {
            foreach ($data as $key => $value) {
                $newData[$key] = array_slice($value, 0, 12);
                $oldData[$key] = array_slice($value, 12);
            }

            foreach ($newData AS $key => $arrNewData) {
                $newData[$key] = serialize($arrNewData);
            }
            $newData = array_unique($newData);
            foreach ($newData AS $key => $strNewData) {
                $newData[$key] = unserialize($strNewData);
            }

            $checkedData = array();
            foreach ($newData as $key => $value) {
                $checkedData[] = array_merge($value, $oldData[$key]);
            }

            /**
             * @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate $resource
             */
            $resource = $this->_objectManager->get('Amasty\ShippingTableRates\Model\ResourceModel\Rate');
            $errText = $resource->batchInsert($methodId, $checkedData);
            if ($errText) {
                $err[] = 'Line #' . $currLineNum . ': duplicated conditions before this line have been skipped';
            } else {
                $err[] = 'Your csv file has been automatically cleared of duplicates and successfully uploaded';
            }
        }

        return $err;
    }

    public function findBy($request, $collection)
    {
        if (!$request->getAllItems()) {
            return array();
        }

        if ($collection->getSize() == 0) {
            return array();
        }

        $methodIds = array();
        foreach ($collection as $method) {
            $methodIds[] = $method->getId();
        }

        // calculate price and weight
        $allowFreePromo = $this->_scopeConfig->getValue('carriers/amstrates/allow_promo');
        $ignoreVirtual = $this->_scopeConfig->getValue('carriers/amstrates/ignore_virtual');

        $configurableSetting = $this->_scopeConfig->getValue('carriers/amstrates/configurable_child');
        $bundleSetting = $this->_scopeConfig->getValue('carriers/amstrates/bundle_child');

        $items = $request->getAllItems();

        $collectedTypes = array();
        $isFreeShipping = 0;

        foreach ($items as $item) {

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if ($item->getParentItemId())
                continue;

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1')
            ) {
                foreach ($item->getChildren() as $child) {
                    $this->getShippingTypes($child);
                }

            } else {
                $this->getShippingTypes($item);
            }
            $address = $item->getAddress();
            if ($address->getFreeShipping()) {
                $isFreeShipping = 1;
            }
        }

        $this->_shippingTypes = $this->_existingShippingTypes;
        $this->_shippingTypes[] = 0;

        $this->_shippingTypes = array_unique($this->_shippingTypes);
        $this->_existingShippingTypes = array_unique($this->_existingShippingTypes);

        $allCosts = [];

        /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate\Collection $allRates */
        $allRates = $this->getResourceCollection();
        $allRates->addMethodFilters($methodIds);
        $ratesTypes = [];

        foreach ($allRates as $singleRate) {
            $ratesTypes[$singleRate->getMethodId()][] = $singleRate->getShippingType();
        }

        $intersectTypes = [];
        $freeTypes = [];

        foreach ($ratesTypes as $key => $value) {
            $intersectTypes[$key] = array_intersect($this->_shippingTypes, $value);
            arsort($intersectTypes[$key]);
            $methodIds = array($key);
            $allTotals = $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo, '0');

            foreach ($intersectTypes[$key] as $shippingType) {

                $totals = $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo, $shippingType);

                if ($allTotals['qty'] > 0 && (!$this->_scopeConfig->getValue('carriers/amstrates/dont_split') || $allTotals['qty'] == $totals['qty'])) {

                    if ($shippingType == 0)
                        $totals = $allTotals;

                    $allTotals['not_free_price'] -= $totals['not_free_price'];
                    $allTotals['not_free_weight'] -= $totals['not_free_weight'];
                    $allTotals['not_free_qty'] -= $totals['not_free_qty'];
                    $allTotals['qty'] -= $totals['qty'];

                    /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate\Collection $allRates */
                    $allRates = $this->getResourceCollection();
                    $allRates->addAddressFilters($request);
                    $allRates->addTotalsFilters($totals, $shippingType, $request, $allowFreePromo);
                    $allRates->addMethodFilters($methodIds);
                    foreach ($this->calculateCosts($allRates, $totals, $request, $shippingType) as $key => $cost) {
                        if (($totals['not_free_qty'] < 1) && ($totals['qty'] < 1))
                            continue;

                        if ($totals['not_free_qty'] < 1)
                            $cost['cost'] = 0;

                        /**
                         * @var \Amasty\ShippingTableRates\Model\Rate $method
                         */
                        $method = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method')->load($key);
                        if (empty($allCosts[$key])) {
                            $allCosts[$key]['cost'] = $cost['cost'];
                            $allCosts[$key]['time'] = $cost['time'];
                        } else {
                            $allCosts = $this->_setCostTime($method, $allCosts, $key, $cost);
                        }

                        $collectedTypes[$key][] = $shippingType;
                        $freeTypes[$key]= $method->getFreeTypes();
                    }
                }
            }
        }

        $allCosts = $this->_unsetUnnecessaryCosts($allCosts, $collectedTypes, $freeTypes);

        /* @var \Amasty\ShippingTableRates\Model\Method $method */
        $method = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method');
        $minRates = $method->getCollection()->hashMinRate();
        $maxRates = $method->getCollection()->hashMaxRate();

        $allCosts = $this->_includeMinMaxRates($allCosts, $maxRates, $minRates);
        $allCosts = $this->applyFreeShipping($allCosts, $isFreeShipping);

        return $allCosts;
    }

    public function initTotals()
    {
        $totals = array(
            'not_free_price' => 0,
            'not_free_weight' => 0,
            'qty' => 0,
            'not_free_qty' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
        );

        return $totals;
    }

    protected function _setDefaultLineValues($line)
    {
        if (!$line[self::PRICE_TO]) $line[self::PRICE_TO] = self::MAX_VALUE;
        if (!$line[self::WEIGHT_TO]) $line[self::WEIGHT_TO] = self::MAX_VALUE;
        if (!$line[self::QTY_TO]) $line[self::QTY_TO] = self::MAX_VALUE;

        return $line;
    }

    protected function _prepareLineTypes($line, $err, $currLineNum, $typeLabels)
    {
        $types = [''];
        if ($line[self::SHIPPING_TYPE]) {
            $types = explode(',', $line[self::SHIPPING_TYPE]);
        }

        foreach ($types as $type) {
            if ($type == 'All') {
                $type = 0;
            }
            if ($type && empty($typeLabels[$type])) {
                if (in_array($type, $typeLabels)) {
                    $typeLabels[$type] = array_search($type, $typeLabels);
                } else {
                    $err[] = 'Line #' . $currLineNum . ': invalid type code ' . $type;
                    continue;
                }

            }
            $line[self::SHIPPING_TYPE] = $type ? $typeLabels[$type] : '';
        }

        return ['line' => $line, 'err' => $err];
    }

    protected function _prepareLineStates($line, $err, $currLineNum, $country, $methodId)
    {
        $dataIndex = 0;

        $states = [''];
        if ($line[self::STATE]) {
            $states = explode(',', $line[self::STATE]);
        }
        $zips = [''];
        if ($line[self::ZIP_FROM]) {
            $zips = explode(',', $line[self::ZIP_FROM]);
        }
        $stateNames = $this->_helper->getStates(true);
        $stateCodes = $this->_helper->getStates();

        foreach ($states as $state) {

            if ($state == 'All') {
                $state = '';
            }

            if ($state && empty($stateCodes[$state][$country])) {
                if (in_array($state, $stateNames)) {
                    $stateCodes[$state][$country] = array_search($state, $stateNames);
                } else {
                    $err[] = 'Line #' . $currLineNum . ': invalid state code ' . $state;
                    continue;
                }

            }
            $line[self::STATE] = $state ? $stateCodes[$state][$country] : '';

            foreach ($zips as $zip) {
                $line[self::ZIP_FROM] = $zip;
                $data[$dataIndex] = $line;
                $dataIndex++;

                if ($dataIndex > self::BATCH_SIZE) {
                    $err = $this->returnErrors($data, $methodId, $currLineNum, $err);
                    $data = [];
                    $dataIndex = 0;
                }
            }
        }

        if (!empty($data)) {
            $this->_data = array_merge($this->_data, $data);
        }

        return ['line' => $line, 'err' => $err, 'data_index' => $dataIndex, 'data' => $this->_data];
    }

    protected function _setCostTime($method, $allCosts, $key, $cost)
    {
        switch ($method->getSelectRate()) {
            case self::ALGORITHM_MAX:
                if ($allCosts[$key]['cost'] < $cost['cost']) {
                    $allCosts[$key]['cost'] = $cost['cost'];
                    $allCosts[$key]['time'] = $cost['time'];
                }
                break;
            case self::ALGORITHM_MIN:
                if ($allCosts[$key]['cost'] > $cost['cost']) {
                    $allCosts[$key]['cost'] = $cost['cost'];
                    $allCosts[$key]['time'] = $cost['time'];
                }
                break;
            default:
                $allCosts[$key]['cost'] += $cost['cost'];
                $allCosts[$key]['time'] = $cost['time'];
        }

        return $allCosts;
    }

    protected function _includeMinMaxRates($allCosts, $maxRates, $minRates)
    {
        foreach ($allCosts as $key => $rate) {
            if ($maxRates[$key] != '0.00' && $maxRates[$key] < $rate['cost']) {
                $allCosts[$key]['cost'] = $maxRates[$key];
            }

            if ($minRates[$key] != '0.00' && $minRates[$key] > $rate['cost']) {
                $allCosts[$key]['cost'] = $minRates[$key];
            }
        }

        return $allCosts;
    }
    
    protected function applyFreeShipping($allCosts, $isFreeShipping)
    {
        if ($isFreeShipping) {
            foreach ($allCosts as $key => $rate) {
                $allCosts[$key]['cost'] = 0;
            }
        }
        return $allCosts;
    }

    protected function _unsetUnnecessaryCosts($allCosts, $collectedTypes, $freeTypes)
    {
        //do not show method if quote has "unsuitable" items
        foreach ($allCosts as $key => $cost){
            //1.if the method contains rate with type == All
            if (in_array('0',$collectedTypes[$key]))
                continue;
            //2.if the method rates contain types for every items in quote
            $extraTypes = array_diff($this->_existingShippingTypes,$collectedTypes[$key]);
            if (!$extraTypes)
                continue;
            //3.if the method free types contain types for every item didn't pass (2)
            if (!array_diff($extraTypes, $freeTypes[$key]))
                continue;

            //else — do not show the method;
            unset($allCosts[$key]);
        }

        return $allCosts;
    }

    protected function getShippingTypes($item)
    {
        /* @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getEntityId());
        if ($product->getAmShippingType()) {
            $this->_existingShippingTypes[] = $product->getAmShippingType();
        } else {
            $this->_existingShippingTypes[] = 0;
        }
    }

    protected function calculateTotals($request, $ignoreVirtual, $allowFreePromo, $shippingType)
    {
        $totals = $this->initTotals();

        //reload child items
        $configurableSetting = $this->_scopeConfig->getValue('carriers/amstrates/configurable_child');
        $bundleSetting = $this->_scopeConfig->getValue('carriers/amstrates/bundle_child');

        foreach ($request->getAllItems() as $item) {
            if ($this->_needSkipItem($item, $ignoreVirtual)) {
                continue;
            }

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1')
            ) {
                $qty = 0;
                $notFreeQty = 0;
                $price = 0;
                $weight = 0;
                $itemQty = 0;

                foreach ($item->getChildren() as $child) {
                    $flagOfPersist = false;
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($child->getProduct()->getEntityId());

                    if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
                        continue;

                    $flagOfPersist = true;
                    $itemQty = $child->getQty() * $item->getQty();
                    $qty += $itemQty;
                    $notFreeQty += ($itemQty - $this->getFreeQty($child, $allowFreePromo));
                    $price += $child->getPrice() * $itemQty;
                    $weight += $child->getWeight() * $itemQty;
                    $totals['tax_amount'] += $child->getBaseTaxAmount() + $child->getBaseHiddenTaxAmount() + $item->getWeeeTaxAppliedAmount();
                    $totals['discount_amount'] += $child->getBaseDiscountAmount();
                }

                if ($typeId == 'bundle') {
                    if ($flagOfPersist == false)
                        continue;
                    //  $qty        = $item->getQty();

                    if ($item->getProduct()->getWeightType() == 1) {
                        $weight = $item->getWeight();
                    }

                    if ($item->getProduct()->getPriceType() == 1) {
                        $price = $item->getPrice();
                    }

                    if ($item->getProduct()->getSkuType() == 1) {
                        $totals['tax_amount'] += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() + $item->getWeeeTaxAppliedAmount();
                        $totals['discount_amount'] += $item->getBaseDiscountAmount();
                    }

                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty'] += $qty;
                    $totals['not_free_qty'] += $notFreeQty;
                    $totals['not_free_price'] += $price;
                    $totals['not_free_weight'] += $weight;

                } elseif ($typeId == 'configurable') {

                    if ($flagOfPersist == false)
                        continue;


                    $qty = $item->getQty();
                    $price = $item->getPrice();
                    $weight = $item->getWeight();
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty'] += $qty;
                    $totals['not_free_qty'] += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                    $totals['tax_amount'] += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() + $item->getWeeeTaxAppliedAmount();
                    $totals['discount_amount'] += $item->getBaseDiscountAmount();

                } else { // for grouped and custom not simple products
                    $qty = $item->getQty();
                    $price = $item->getPrice();
                    $weight = $item->getWeight();
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty'] += $qty;
                    $totals['not_free_qty'] += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                }

            } else {
                /* @var \Magento\Catalog\Model\Product $product */
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getEntityId());

                if ($this->_needSkipSimpleItem($product, $shippingType, $item)) {
                    continue;
                }

                $qty = $item->getQty();
                $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                $totals['not_free_price'] += $item->getBasePrice() * $notFreeQty;
                $totals['not_free_weight'] += $item->getWeight() * $notFreeQty;
                $totals['qty'] += $qty;
                $totals['not_free_qty'] += $notFreeQty;
                $totals['tax_amount'] += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() + $item->getWeeeTaxAppliedAmount();
                $totals['discount_amount'] += $item->getBaseDiscountAmount();
            }


        }// foreach

        // fix magento bug
        if ($totals['qty'] != $totals['not_free_qty'])
            $request->setFreeShipping(false);

        $afterDiscount = $this->_scopeConfig->getValue('carriers/amstrates/after_discount');
        $includingTax = $this->_scopeConfig->getValue('carriers/amstrates/including_tax');

        if ($afterDiscount)
            $totals['not_free_price'] -= $totals['discount_amount'];

        if ($includingTax)
            $totals['not_free_price'] += $totals['tax_amount'];

        if ($totals['not_free_price'] < 0)
            $totals['not_free_price'] = 0;

        if ($request->getFreeShipping() && $allowFreePromo)
            $totals['not_free_price'] = $totals['not_free_weight'] = $totals['not_free_qty'] = 0;

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }


        return $totals;
    }

    protected function _needSkipItem($item, $ignoreVirtual)
    {
        $needSkipItem = false;

        if ($item->getParentItemId())
            $needSkipItem = true;

        if ($ignoreVirtual && $item->getProduct()->isVirtual())
            $needSkipItem = true;

        return $needSkipItem;
    }

    protected function _needSkipSimpleItem($product, $shippingType, $item)
    {
        $needSkipSimpleItem = false;

        if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
            $needSkipSimpleItem = true;

        if ($item->getParentItemId())
            $needSkipSimpleItem = true;

        return $needSkipSimpleItem;
    }

    protected function calculateCosts($allRates, $totals, $request, $shippingType)
    {
        $shippingFlatParams = ['country', 'state'];
        $shippingRangeParams = ['price', 'qty', 'weight'];

        $minCounts = [];   // min empty values counts per method
        $results = [];
        foreach ($allRates as $rate) {

            $rate = $rate->getData();

            $emptyValuesCount = 0;

            if (empty($rate['shipping_type'])) {
                $emptyValuesCount++;
            }

            foreach ($shippingFlatParams as $param) {
                if (empty($rate[$param])) {
                    $emptyValuesCount++;
                }
            }

            foreach ($shippingRangeParams as $param) {
                if ((ceil($rate[$param . '_from']) == 0) && (ceil($rate[$param . '_to']) == self::MAX_VALUE)) {
                    $emptyValuesCount++;
                }
            }

            if (empty($rate['zip_from']) && empty($rate['zip_to'])) {
                $emptyValuesCount++;
            }

            if (!$totals['not_free_price'] && !$totals['not_free_qty'] && !$totals['not_free_weight']) {
                $cost = 0;
            } else {
                $cost = $rate['cost_base'] + $totals['not_free_price'] * $rate['cost_percent'] / 100 + $totals['not_free_qty'] * $rate['cost_product'] + $totals['not_free_weight'] * $rate['cost_weight'];
            }

            $id = $rate['method_id'];
            if ((empty($minCounts[$id]) && empty($results[$id])) || ($minCounts[$id] > $emptyValuesCount) || (($minCounts[$id] == $emptyValuesCount) && ($cost > $results[$id]))) {
                $minCounts[$id] = $emptyValuesCount;
                $results[$id]['cost'] = $cost;
                $results[$id]['time'] = $rate['time_delivery'];
                $results[$id]['shipping_type'] = $rate['shipping_type'];
            }

        }

        return $results;
    }
}
