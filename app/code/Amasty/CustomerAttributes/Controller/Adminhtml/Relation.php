<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml;

abstract class Relation extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_CustomerAttributes::attributes_relation';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Amasty\CustomerAttributes\Api\RelationRepositoryInterface
     */
    protected $relationRepository;

    /**
     * @var \Amasty\CustomerAttributes\Model\RelationFactory
     */
    protected $relationFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Relation constructor.
     *
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Amasty\CustomerAttributes\Api\RelationRepositoryInterface $relationRepository
     * @param \Amasty\CustomerAttributes\Model\RelationFactory    $relationFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\CustomerAttributes\Api\RelationRepositoryInterface $relationRepository,
        \Amasty\CustomerAttributes\Model\RelationFactory $relationFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->relationRepository = $relationRepository;
        $this->relationFactory = $relationFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_CustomerAttributes::attributes_relation')
            ->addBreadcrumb(__('Customer'), __('Customer'))
            ->addBreadcrumb(__('Attribute Relation'), __('Attribute Relation'));
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Attribute Relation'));
        return $resultPage;
    }
}
