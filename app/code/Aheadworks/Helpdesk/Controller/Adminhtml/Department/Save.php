<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::departments';

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var DepartmentInterfaceFactory
     */
    private $departmentFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param DepartmentInterfaceFactory $departmentFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DepartmentRepositoryInterface $departmentRepository,
        DepartmentInterfaceFactory $departmentFactory,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->departmentRepository = $departmentRepository;
        $this->departmentFactory = $departmentFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                $id = isset($data['id']) ? $data['id'] : false;
                $preparedData = $this->prepareData($data);

                /** @var DepartmentInterface $departmentDataObject */
                $departmentDataObject = $id
                    ? $this->departmentRepository->getById($id)
                    : $this->departmentFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $departmentDataObject,
                    $preparedData,
                    DepartmentInterface::class
                );

                $departmentDataObject = $this->departmentRepository->save($departmentDataObject);

                $this->messageManager->addSuccessMessage(__('Department was successfully saved'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $departmentDataObject->getId()]);
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the department data')
                );
            }
            $this->dataPersistor->set('aw_helpdesk_department', $data);
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * Prepare data before save
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data)
    {
        $preparedData = [];
        foreach ($data as $key => $value) {
            if ($key == 'store_labels') {
                $storeLabels = [];
                foreach ($value as $item) {
                    if (!isset($item['delete'])) {
                        $storeLabels[] = [
                            'store_id'  => $item['store_id'],
                            'label'     => $item['label']
                        ];
                    }
                }
                $preparedData[$key] = $storeLabels;
            } else {
                $preparedData[$key] = $value;
            }
        }
        return $preparedData;
    }
}
