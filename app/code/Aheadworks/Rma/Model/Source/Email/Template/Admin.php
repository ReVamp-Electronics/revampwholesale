<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\Email\Template;

/**
 * Class Admin
 * @package Aheadworks\Rma\Model\Source\Email\Template
 */
class Admin implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $emailTemplates;

    /**
     * @var \Magento\Email\Model\Template\Config\Data
     */
    protected $dataStorage;

    /**
     * @var string|null
     */
    protected $path = null;

    /**
     * @param \Magento\Config\Model\Config\Source\Email\Template $emailTemplates
     * @param \Magento\Email\Model\Template\Config\Data $dataStorage
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplates,
        \Magento\Email\Model\Template\Config\Data $dataStorage
    ) {
        $this->emailTemplates = $emailTemplates;
        $this->dataStorage = $dataStorage;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $path = 'aw_rma_email_template_to_admin_thread';
        if ($this->path) {
            $data = $this->dataStorage->get();
            if (isset($data[$this->path])) {
                $path = $this->path;
            }
        }
        return $this->emailTemplates->setPath($path)->toOptionArray();
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * @param int $value
     * @return null|\Magento\Framework\Phrase
     */
    public function getOptionLabelByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
