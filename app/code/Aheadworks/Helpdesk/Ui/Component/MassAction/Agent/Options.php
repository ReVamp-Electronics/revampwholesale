<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Ui\Component\MassAction\Agent;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 * @package Aheadworks\Helpdesk\Ui\Component\MassAction\Agent
 */
class Options implements JsonSerializable
{
    /**
     * Options
     * @var array
     */
    protected $options;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Agent source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    protected $agentSource;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource,
        array $data = []
    ) {
        $this->agentSource = $agentSource;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $options = $this->agentSource->getAvailableOptions();
            $this->prepareData();
            foreach ($options as $key => $optionCode) {
                $this->options[$key] = [
                    'type' => 'agent' . $key,
                    'label' => $optionCode,
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$key]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $key]
                    );
                }

                $this->options[$key] = array_merge_recursive(
                    $this->options[$key],
                    $this->additionalData
                );
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
