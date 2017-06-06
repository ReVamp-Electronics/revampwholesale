<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;

/**
 * Class DeleteButton
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Department\Edit
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @param Context $context
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        Context $context,
        DepartmentRepositoryInterface $departmentRepository
    ) {
        $this->context = $context;
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($id = $this->getId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s')",
                    __('Are you sure you want to do this?'),
                    $this->getUrl('*/*/delete', ['id' => $id])
                ),
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Return department ID
     *
     * @return int|null
     */
    public function getId()
    {
        $id = $this->context->getRequest()->getParam('id');

        if ($id && $this->departmentRepository->getById($id)) {
            return $id;
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
