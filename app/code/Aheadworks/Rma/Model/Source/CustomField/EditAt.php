<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\CustomField;

/**
 * Class EditAt
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class EditAt extends \Aheadworks\Rma\Model\Source\Request\Status
{
    const NEW_REQUEST_PAGE          = -1;

    const NEW_REQUEST_PAGE_LABEL    = 'New Request Page';

    /**
     * @return array
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $options = [
                self::NEW_REQUEST_PAGE => __(self::NEW_REQUEST_PAGE_LABEL)
            ];
            foreach (parent::getOptions() as $value => $label) {
                $options[$value] = $label;
            }
            $this->options = $options;
        }
        return $this->options;
    }
}
