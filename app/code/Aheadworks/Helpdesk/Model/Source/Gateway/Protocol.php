<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source\Gateway;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Protocol
 * @package Aheadworks\Helpdesk\Model\Source\Gateway
 */
class Protocol implements OptionSourceInterface
{
    const POP3_VALUE = 'POP3';
    const IMAP_VALUE = 'IMAP';

    const POP3_LABEL = 'POP3';
    const IMAP_LABEL = 'IMAP';

    const POP3_INSTANCE = 'Zend_Mail_Storage_Pop3';
    const IMAP_INSTANCE = 'Zend_Mail_Storage_Imap';

    public function toOptionArray()
    {
        return array(
            array('value' => self::POP3_VALUE, 'label' => __(self::POP3_LABEL)),
            array('value' => self::IMAP_VALUE, 'label' => __(self::IMAP_LABEL)),
        );
    }

    /**
     * Get instance by protocol
     * @param int $protocol
     * @return null | string
     */
    public function getInstanceByProtocol($protocol)
    {
        switch ($protocol) {
            case self::POP3_VALUE : $instance = self::POP3_INSTANCE;
                break;
            case self::IMAP_VALUE : $instance = self::IMAP_INSTANCE;
                break;
            default : $instance = null;
        }
        return $instance;
    }
}
