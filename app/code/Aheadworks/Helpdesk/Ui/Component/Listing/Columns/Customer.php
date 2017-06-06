<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Customer
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns
 */
class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Store repository
     *
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * Customer repository model (by default)
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['customer_name'] = $this->prepareContent($item['customer_id'], $item['customer_name'], $item['customer_email'], $item['store_id']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $customerId
     * @param $customerName
     * @return string
     */
    protected function prepareContent($customerId, $customerName, $customerEmail, $storeId)
    {
        $result = $customerName;
        try {
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
            } else {
                $storeData = $this->storeRepository->getById($storeId);
                $websiteId = $storeData->getWebsiteId();
                $customer = $this->customerRepository->get($customerEmail, $websiteId);
            }
        } catch (\Exception $e) {
            $customer = null;
        }
        if (!$customer) {
            return $result;
        }
        try {
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
            $url = $this->context->getUrl('customer/index/edit', ['id' => $customer->getId()]);
            $result =  '<a href="' . $url . '">' . $name . '</a>';
        } catch (\Exception $e) {
            //do nothing when customer isn't exist
        }

        return $result;
    }
}