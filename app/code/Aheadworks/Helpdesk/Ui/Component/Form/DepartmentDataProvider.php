<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DepartmentDataProvider
 * @package Aheadworks\Helpdesk\Ui\Component\Form
 * @codeCoverageIgnore
 */
class DepartmentDataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        DepartmentRepositoryInterface $departmentRepository,
        DataObjectProcessor $dataObjectProcessor,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->departmentRepository = $departmentRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get('aw_helpdesk_department');
        if (!empty($dataFromForm)) {
            if (isset($dataFromForm['id'])) {
                $data[$dataFromForm['id']] = $dataFromForm;
            } else {
                $data[null] = $dataFromForm;
            }
            $this->dataPersistor->clear('aw_helpdesk_department');
        } else {
            $id = $this->request->getParam($this->getRequestFieldName());
            if ($id) {
                /** @var DepartmentInterface $departmentDataObject */
                $departmentDataObject = $this->departmentRepository->getById($id);

                $formData = $this->dataObjectProcessor->buildOutputDataArray(
                    $departmentDataObject,
                    DepartmentInterface::class
                );

                $formData = $this->convertToString(
                    $formData,
                    [
                        DepartmentInterface::IS_ENABLED,
                        DepartmentInterface::IS_VISIBLE,
                        DepartmentInterface::IS_DEFAULT,
                        DepartmentInterface::WEBSITE_IDS,
                        DepartmentInterface::GATEWAY,
                        DepartmentInterface::PERMISSIONS
                    ]
                );
                $data[$departmentDataObject->getId()] = $formData;
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }

    /**
     * Convert selected fields to string
     *
     * @param [] $data
     * @param string[] $fields
     * @return []
     */
    private function convertToString($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    foreach ($data[$field] as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $childKey => $childValue) {
                                if ($childValue === false) {
                                    $data[$field][$key][$childKey] = '0';
                                } else {
                                    $data[$field][$key][$childKey] = (string)$childValue;
                                }
                            }
                        } else {
                            if ($value === false) {
                                $data[$field][$key] = '0';
                            } else {
                                $data[$field][$key] = (string)$value;
                            }
                        }
                    }
                } else {
                    $data[$field] = (string)$data[$field];
                }
            }
        }
        return $data;
    }
}
