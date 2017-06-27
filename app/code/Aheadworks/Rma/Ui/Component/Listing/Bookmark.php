<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Ui\Component\Listing;

use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Class Bookmark
 */
class Bookmark extends \Magento\Ui\Component\Bookmark
{
    const RMA_LISTING_NAMESPACE = 'aw_rma_listing';

    /**
     * @var \Magento\Ui\Api\Data\BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getConfiguration($this);
        if (!isset($config['views'])) {
            $this->addView('default', __('Default View'), ['payment_method']);
            $this->addView(
                'pending_approval',
                __('Pending Approval'),
                ['payment_method', 'status_id', 'updated_at'],
                ['status_id' => [(string)Status::PENDING_APPROVAL]]
            );
            $this->addView(
                'package_sent',
                __('Package Sent'),
                ['payment_method', 'status_id', 'created_at'],
                ['status_id' => [(string)Status::PACKAGE_SENT]]
            );
            $this->addView(
                'package_received',
                __('Package Received'),
                ['payment_method', 'created_at'],
                ['status_id' => [(string)Status::PACKAGE_RECEIVED]]
            );
            $this->addView(
                'issue_refund',
                __('Issue Refund'),
                ['created_at'],
                ['status_id' => [(string)Status::ISSUE_REFUND]]
            );
        }
    }

    /**
     * Add view to the current config and save the bookmark to db
     * @param $index
     * @param $label
     * @param array $hideColumns columns to hide comparing to default view config
     * @param array $filters applied filters as $filterName => $filterValue array
     * @return $this
     */
    public function addView($index, $label, $hideColumns = [], $filters = [])
    {
        $config = $this->getConfiguration($this);

        $viewConf = $this->getDefaultViewConfig();
        $viewConf = array_merge($viewConf, [
            'index'     => $index,
            'label'     => $label,
            'value'     => $label,
            'editable'  => false
        ]);
        foreach ($hideColumns as $hideColumn) {
            $viewConf['data']['columns'][$hideColumn]['visible'] = false;
        }
        foreach ($filters as $filterName => $filterValue) {
            $viewConf['data']['filters']['applied'][$filterName] = $filterValue;
        }
        $viewConf['data']['displayMode'] = 'grid';

        $this->_saveBookmark($index, $label, $viewConf);

        $config['views'][$index] = $viewConf;
        $this->setData('config', array_replace_recursive($config, $this->getConfiguration($this)));
        return $this;
    }

    /**
     * Save bookmark to db
     * @param $index
     * @param $label
     * @param $viewConf
     */
    protected function _saveBookmark($index, $label, $viewConf)
    {
        $bookmark = $this->bookmarkFactory->create();
        $config = ['views' => [$index => $viewConf]];
        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace(self::RMA_LISTING_NAMESPACE)
            ->setIdentifier($index)
            ->setTitle($label)
            ->setConfig(json_encode($config));
        $this->bookmarkRepository->save($bookmark);
    }

    /**
     * @return mixed
     */
    public function getDefaultViewConfig()
    {
        $config['editable']  = false;
        $config['data']['filters']['applied']['placeholder'] = true;
        $config['data']['columns'] = [
            'id'            => ['sorting' => 'desc', 'visible' => true],
            'order_id'      => ['sorting' => false, 'visible' => true],
            'payment_method'=> ['sorting' => false, 'visible' => true],
            'customer_id'   => ['sorting' => false, 'visible' => true],
            'products'      => ['sorting' => false, 'visible' => true],
            'cf1_value'     => ['sorting' => false, 'visible' => true],//todo
            'last_reply_by' => ['sorting' => false, 'visible' => true],
            'status_id'     => ['sorting' => false, 'visible' => true],
            'store_id'      => ['sorting' => false, 'visible' => true],
            'updated_at'    => ['sorting' => false, 'visible' => true],
            'created_at'    => ['sorting' => false, 'visible' => true]
        ];

        $position = 0;
        foreach (array_keys($config['data']['columns']) as $colName) {
            $config['data']['positions'][$colName] = $position;
            $position++;
        }

        $config['data']['paging'] = [
            'options' => [
                20 => ['value' => 20, 'label' => 20],
                30 => ['value' => 30, 'label' => 30],
                50 => ['value' => 50, 'label' => 50],
                100 => ['value' => 30, 'label' => 30],
                200 => ['value' => 30, 'label' => 30]
            ],
            'value' => 20
        ];
        return $config;
    }
}
