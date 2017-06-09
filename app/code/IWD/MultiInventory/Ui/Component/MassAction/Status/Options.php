<?php

namespace IWD\MultiInventory\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use IWD\MultiInventory\Model\Config\Source\Order\Statuses;

class Options implements JsonSerializable
{
    /**
     * @var Statuses
     */
    protected $_statuses;

    /**
     * @var array
     */
    protected $_options;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $_data;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $_urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $_paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $_additionalData = [];

    /**
     * @param UrlInterface $urlBuilder
     * @param Statuses $statuses
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Statuses $statuses,
        array $data = []
    ) {
        $this->_data = $data;
        $this->_statuses = $statuses;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->_options === null) {
            $options = $this->_statuses->toOptionArray();
            $this->prepareData();

            foreach ($options as $optionCode) {
                $value = $optionCode['value'];

                $this->_options[$value] = [
                    'type' => $value,
                    'label' => $optionCode['label'],
                ];

                if ($this->_urlPath && $this->_paramName) {
                    $this->_options[$value]['url'] = $this->_urlBuilder->getUrl(
                        $this->_urlPath,
                        [$this->_paramName => $value]
                    );
                }

                $this->_options[$value] = array_merge_recursive(
                    $this->_options[$value],
                    $this->_additionalData
                );
            }

            $this->_options = array_values($this->_options);
        }

        return $this->_options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
                default:
                    $this->_additionalData[$key] = $value;
                    break;
            }
        }
    }
}
