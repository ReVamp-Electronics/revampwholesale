<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Config
 * @package Aheadworks\Helpdesk\Model
 */
class Config extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Last time HDU create ticket job ran
     */
    const LAST_EXEC_TIME_CREATE_TICKET = 'last_exec_time_create_ticket';

    /**
     * Last time HDU create mail job ran
     */
    const LAST_EXEC_TIME_CREATE_MAIL = 'last_exec_time_create_mail';

    /**
     * Last time HDU create automation job ran
     */
    const LAST_EXEC_TIME_CREATE_AUTOMATION = 'last_exec_time_create_automation';

    /**
     * Last time HDU run automation job ran
     */
    const LAST_EXEC_TIME_RUN_AUTOMATION = 'last_exec_time_run_automation';

    /**
     * Last time HDU update automation job ran
     */
    const LAST_EXEC_TIME_UPDATE_AUTOMATION = 'last_exec_time_update_automation';

    /**
     * Error email message group name
     */
    const EMAIL_ERROR_MESSAGE_GROUP = 'aw_helpdesk_email';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductMetadataInterface $productMetadata
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductMetadataInterface $productMetadata,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initial resource model
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Config');
    }

    /**
     * Set param
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->unsetData();
        $this->_getResource()->load($this, $name, 'name');
        $this->addData([
            'name' => $name,
            'value' => $value
        ]);
        $this->_getResource()->save($this);
        return $this;
    }

    /**
     * Get param
     * @param $name
     * @return mixed
     */
    public function getParam($name)
    {
        $this->unsetData();
        $this->_getResource()->load($this, $name, 'name');

        return $this->getData('value');
    }

    /**
     * Check if Magento is Enterprise Edition
     *
     * @return bool
     */
    public function isEE()
    {
        return ($this->productMetadata->getEdition() == 'Enterprise');
    }
}