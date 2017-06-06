<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source\Gateway;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Secure
 * @package Aheadworks\Helpdesk\Model\Source\Gateway
 */
class Secure implements OptionSourceInterface
{
    const TYPE_NONE_VALUE = '0';
    const TYPE_SSL_VALUE  = 'SSL';
    const TYPE_TLS_VALUE  = 'TLS';

    const TYPE_NONE_LABEL = 'None';
    const TYPE_SSL_LABEL  = 'SSL';
    const TYPE_TLS_LABEL  = 'TLS';

    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::TYPE_NONE_VALUE, 'label' => __(self::TYPE_NONE_LABEL)),
            array('value' => self::TYPE_SSL_VALUE, 'label' => __(self::TYPE_SSL_LABEL)),
            array('value' => self::TYPE_TLS_VALUE, 'label' => __(self::TYPE_TLS_LABEL)),
        );
    }
}