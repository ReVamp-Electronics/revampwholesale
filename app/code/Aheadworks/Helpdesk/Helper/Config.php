<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Helper;

/**
 * Class Config
 * @package Aheadworks\Helpdesk\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Agent config path
     */
    const XML_PATH_AGENTS_USER = 'aw_helpdesk/general/agent_users';

    /**
     * Get available agents
     *
     * @return mixed
     */
    public function getAgents()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AGENTS_USER
        );
    }
}
