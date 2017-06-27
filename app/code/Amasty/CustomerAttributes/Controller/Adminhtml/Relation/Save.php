<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml\Relation;

class Save extends \Amasty\CustomerAttributes\Controller\Adminhtml\Relation
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory                 $resultPageFactory
     * @param \Amasty\CustomerAttributes\Api\RelationRepositoryInterface $relationRepository
     * @param \Amasty\CustomerAttributes\Model\RelationFactory           $relationFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface      $dataPersistor
     * @param \Psr\Log\LoggerInterface                                   $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\CustomerAttributes\Api\RelationRepositoryInterface $relationRepository,
        \Amasty\CustomerAttributes\Model\RelationFactory $relationFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $relationRepository, $relationFactory);
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    /**
     * Save Action
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {

            /** @var \Amasty\CustomerAttributes\Model\Relation $model */
            $model = $this->relationFactory->create();

            try {
                $relationId = $this->getRequest()->getParam('relation_id');
                if ($relationId) {
                    $model = $this->relationRepository->get($relationId);
                }

                $this->_getSession()->setPageData($data);
                $this->dataPersistor->set('amasty_customer_attributes_relation', $data);

                $model->loadPost($data);

                $this->relationRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Relation has been saved.'));
                $this->_getSession()->setPageData(false);
                $this->dataPersistor->clear('amasty_customer_attributes_relation');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amcustomerattr/*/edit', ['relation_id' => $model->getId()]);
                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $relationId = (int)$this->getRequest()->getParam('relation_id');
                if (!empty($relationId)) {
                    $this->_redirect('amcustomerattr/*/edit', ['relation_id' => $relationId]);
                } else {
                    $this->_redirect('amcustomerattr/*/new');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The Relation has not been saved. Please review the error log for the details.')
                );
                $this->logger->critical($e);
                $this->_getSession()->setPageData($data);
                $this->dataPersistor->set('amasty_customer_attributes_relation', $data);
                $this->_redirect(
                    'amcustomerattr/*/edit',
                    ['relation_id' => $this->getRequest()->getParam('relation_id')]
                );
                return;
            }
        }
        $this->_redirect('amcustomerattr/*/');
    }
}
