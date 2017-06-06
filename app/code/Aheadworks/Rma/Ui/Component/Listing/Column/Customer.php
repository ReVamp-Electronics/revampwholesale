<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param ContextInterface $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->customerFactory = $customerFactory;
        $this->url = $url;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($customerId = $item[$fieldName]) {
                    $customer = $this->customerFactory->create()->load($customerId);
                    if ($customer->getId()) {
                        $item[$fieldName . '_name'] = $customer->getName();
                        $item[$fieldName . '_email'] = $customer->getEmail();
                        $item[$fieldName . '_url'] = $this->url->getUrl(
                            'customer/index/edit',
                            ['id' => $customer->getId()]
                        );
                    }
                }
            }
        }
        return $dataSource;
    }
}
