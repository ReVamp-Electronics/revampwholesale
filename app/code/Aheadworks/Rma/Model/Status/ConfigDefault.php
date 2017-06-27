<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Status;

class ConfigDefault extends \Magento\Framework\Config\Data
{
    public function __construct(
        \Aheadworks\Rma\Model\Status\ConfigDefault\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'aheadworks_rma_status_config_default_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
