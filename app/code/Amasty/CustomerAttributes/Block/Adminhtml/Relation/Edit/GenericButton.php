<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Block\Adminhtml\Relation\Edit;

use Amasty\CustomerAttributes\Controller\RegistryConstants;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton as CustomerGenericButton;

class GenericButton extends CustomerGenericButton
{
    /**
     * Return the current Catalog Rule Id.
     *
     * @return int|null
     */
    public function getRelationId()
    {
        $entity = $this->registry->registry(RegistryConstants::CURRENT_RELATION_ID);
        return $entity ? $entity->getId() : null;
    }
}
