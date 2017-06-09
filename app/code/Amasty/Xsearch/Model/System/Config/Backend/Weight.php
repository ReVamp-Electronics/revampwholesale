<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\System\Config\Backend;

class Weight extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    protected $_xSearchHelper;

    /**
     * Weight constructor.
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Amasty\Xsearch\Helper\Data $xSearchHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->attributeRepository = $attributeRepository;
        $this->_xSearchHelper = $xSearchHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];
        if (!$value) {
            return $this;
        }
        foreach ($value as $data) {
            if (!$data) {
                continue;
            }
            if (!is_array($data)) {
                continue;
            }
            if (count($data) < 2) {
                continue;
            }
            $result[$data['attributes_weight']] = $data['weight'];
            $this->setWeightAndSearchable($data['attributes_weight'], $data['weight']);
        }
        $this->deactivateSearchable($value);
        $this->setValue(serialize($result));
        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $value = $this->encodeArrayFieldValue($this->getActiveInSearchAttributes());
        $this->setValue($value);
        return $this;
    }

    /**
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $attributes => $weight) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['attributes' => $attributes, 'weight' => $weight];
        }
        return $result;
    }

    /**
     * Get attributes in wich is_searchable true
     * @return array
     */
    private function getActiveInSearchAttributes()
    {
        $productAttributes = $this->_xSearchHelper->getProductAttributes();
        $values = [];
        if (!$productAttributes) {
            return $values;
        }
        foreach ($productAttributes as $attribute) {
            if ($attribute->getIsSearchable()) {
                $values[$attribute->getAttributeCode()] = $attribute->getSearchWeight();
            }
        }
        return $values;
    }

    /**
     * @param $attributeCode
     * @param $weight
     */
    private function setWeightAndSearchable($attributeCode, $weight)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $attribute->setIsSearchable(true);
        $this->attributeRepository->save($attribute);
    }

    /**
     * Set in the attribute is_searchable in false
     * @param $values
     */
    private function deactivateSearchable($values)
    {
        $productAttributes = array_flip($this->_xSearchHelper->getProductAttributes('is_searchable'));
        if (!$values) {
            return;
        }
        foreach ($values as $value) {
            if ($value) {
                unset($productAttributes[$value['attributes_weight']]);
            }
        }
        foreach ($productAttributes as $attribute => $value) {
            $attribute = $this->attributeRepository->get($attribute);
            $attribute->setIsSearchable(false);
            $this->attributeRepository->save($attribute);
        }
    }
}
