<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model;

use Amasty\CustomerAttributes\Api\Data\RelationInterface;
use Amasty\CustomerAttributes\Api\Data\RelationDetailInterface;

/**
 * @method ResourceModel\Relation _getResource
 * @method ResourceModel\Relation getResource
 */
class Relation extends \Magento\Framework\Model\AbstractModel implements RelationInterface
{
    /**
     * @var \Amasty\CustomerAttributes\Model\RelationDetailsFactory
     */
    private $detailsFactory;

    protected $datailsChanged = false;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\CustomerAttributes\Model\ResourceModel\Relation $resource,
        \Amasty\CustomerAttributes\Model\ResourceModel\Relation\Collection $resourceCollection,
        \Amasty\CustomerAttributes\Model\RelationDetailsFactory $detailsFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->detailsFactory = $detailsFactory;
    }

    public function _construct()
    {
        $this->_init('Amasty\CustomerAttributes\Model\ResourceModel\Relation');
    }

    /**
     * Initialize relation model data from array.
     *
     * @param array $data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadPost(array $data)
    {
        if (!isset($data['name'])
            || !isset($data['attribute_id'])
            || !isset($data['attribute_options'])
            || !isset($data['dependent_attributes'])
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Data is incorrect.'));
        }
        $this->setName($data['name']);

        $details = [];

        if (isset($data['relation_id']) && $data['relation_id']) {
            $this->deleteExistRelation($data['relation_id']);
        }
        foreach ($data['attribute_options'] as $option) {
            foreach ($data['dependent_attributes'] as $attribute) {
                $details[] = $this->detailsFactory->create()
                    ->setAttributeId($data['attribute_id'])
                    ->setOptionId($option)
                    ->setDependentAttributeId($attribute);
            }
        }
        $this->setDetails($details);

        return $this;
    }

    /**
     * @param $id
     */
    protected function deleteExistRelation($id)
    {
        if ($this->getRelationId()) {
            $collection = $this->detailsFactory->create()->getCollection()
                ->addFieldToFilter('relation_id', $id);

            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $item->delete();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationId()
    {
        return $this->_getData(self::RELATION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRelationId($relationId)
    {
        $this->setData(self::RELATION_ID, $relationId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails()
    {
        if ($this->getRelationId() && $this->_getData('relation_details') === null) {
            $this->setDetails($this->getResource()->getDetails($this->getRelationId()));
        }

        $details = $this->_getData('relation_details');
        return is_array($details) ? $details : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setDetails($relationDetails)
    {
        $this->setData('relation_details', $relationDetails);
        return $this;
    }

    /**
     * @return int
     */
    public function getAttributeId()
    {
        if ($this->_getData('attribute_id') === null) {
            foreach ($this->getDetails() as $relationDetail) {
                $this->setData('attribute_id', $relationDetail->getAttributeId());
                break;
            }
        }

        return $this->_getData('attribute_id');
    }

    /**
     * @return string
     */
    public function getAttributeOptions()
    {
        if ($this->_getData('attribute_options') === null) {
            $this->setData(
                'attribute_options',
                join(',', $this->getDetailColumnValues(RelationDetailInterface::OPTION_ID))
            );
        }

        return $this->_getData('attribute_options');
    }

    /**
     * @return string
     */
    public function getDependentAttributes()
    {
        if ($this->_getData('dependent_attributes') === null) {
            $this->setData(
                'dependent_attributes',
                join(',', $this->getDetailColumnValues(RelationDetailInterface::DEPENDENT_ATTRIBUTE_ID))
            );
        }

        return $this->_getData('dependent_attributes');
    }

    /**
     * @param string $column
     *
     * @return array
     */
    protected function getDetailColumnValues($column)
    {
        $options = [];
        foreach ($this->getDetails() as $relationDetail) {
            $options[$relationDetail->getId()] = $relationDetail->getData($column);
        }
        return $options;
    }
}
