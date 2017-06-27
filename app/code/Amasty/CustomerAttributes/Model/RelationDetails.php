<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model;

use Amasty\CustomerAttributes\Api\Data\RelationDetailInterface;

class RelationDetails extends \Magento\Framework\Model\AbstractModel implements RelationDetailInterface
{
    public function _construct()
    {
        $this->_init('Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails');
    }

    /**
     * Returns EAV Attribute ID
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->_getData(self::ATTRIBUTE_ID);
    }

    /**
     * @param int $attributeId
     *
     * @return $this
     */
    public function setAttributeId($attributeId)
    {
        $this->setData(self::ATTRIBUTE_ID, $attributeId);

        return $this;
    }

    /**
     * Returns Attribute Option ID
     *
     * @return int
     */
    public function getOptionId()
    {
        return $this->_getData(self::OPTION_ID);
    }

    /**
     * @param int $optionId
     *
     * @return $this
     */
    public function setOptionId($optionId)
    {
        $this->setData(self::OPTION_ID, $optionId);

        return $this;
    }

    /**
     * Returns Dependent EAD Attribute ID
     *
     * @return int
     */
    public function getDependentAttributeId()
    {
        return $this->_getData(self::DEPENDENT_ATTRIBUTE_ID);
    }

    /**
     * @param int $attributeId
     *
     * @return $this
     */
    public function setDependentAttributeId($attributeId)
    {
        $this->setData(self::DEPENDENT_ATTRIBUTE_ID, $attributeId);

        return $this;
    }

    /**
     * Returns Relation ID
     *
     * @return int
     */
    public function getRelationId()
    {
        return $this->_getData(self::RELATION_ID);
    }

    /**
     * @param int $relationId
     *
     * @return $this
     */
    public function setRelationId($relationId)
    {
        $this->setData(self::RELATION_ID, $relationId);

        return $this;
    }
}
