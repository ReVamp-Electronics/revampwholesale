<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Agent;

use Magento\Framework\Data\OptionSourceInterface;


/**
 * Class Options
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Agent
 */
class Options implements OptionSourceInterface
{
    /**
     * Options
     * @var array
     */
    protected $options;

    /**
     * Agent source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    protected $agentSource;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
    ) {
        $this->agentSource = $agentSource;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $availableOptions = $this->agentSource->getAvailableOptions();
            foreach ($availableOptions as $value => $label) {
                $this->options[] = ['value' => $value, 'label' => $label];
            }
        }
        return $this->options;
    }
}