<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\AbstractOptions;

class Options extends AbstractOptions
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'labels',
            'Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab\Options\Labels'
        );
        $this->addChild(
            'options',
            'Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab\Options\Options'
        );

        return $this;
    }
}
