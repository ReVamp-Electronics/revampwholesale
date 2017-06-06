<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Status implements OptionSourceInterface
{
    /**#@+
     * Status values
     */
    const ACTIVE_VALUE      = 'active';
    const INACTIVE_VALUE    = 'inactive';
    /**#@-*/

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTIVE_VALUE,
                'label' => __('Active')
            ],
            [
                'value' => self::INACTIVE_VALUE,
                'label' => __('Inactive')
            ],
        ];
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = $this->toOptionArray();
        $result = [];
        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}
