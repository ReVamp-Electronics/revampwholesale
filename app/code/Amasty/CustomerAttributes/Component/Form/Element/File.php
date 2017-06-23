<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

class File extends AbstractCustomElement
{
    const NAME = 'customer_element_customfile';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    public function __construct(
        ContextInterface $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $options, $components, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * add url path for file
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');

        $path = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $url =  $path . 'amasty/amcustomerattr/files';
        $config['path'] = $url;

        $this->setData('config', (array)$config);

    }
}
