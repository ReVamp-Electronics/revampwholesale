<?php

namespace IWD\OrderManager\Controller\Adminhtml\Creditmemo\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Model\Creditmemo\CreditmemoRepository;
use IWD\OrderManager\Model\Creditmemo\Log\Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Creditmemo\Info
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $creditmemo = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param CreditmemoRepository $creditmemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $actionType
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        CreditmemoRepository $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $actionType = self::ACTION_UPDATE
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            $actionType
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @return string|string[]
     */
    protected function getResultHtml()
    {
        $this->updateCreditmemo();
        return $this->prepareResponse();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->loadCreditmemo()->getOrderId();
    }

    /**
     * @return void
     */
    public function addLogs()
    {
        Logger::getInstance()->saveLogs($this->creditmemo);
    }

    /**
     * @return array
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function updateCreditmemo()
    {
        $this->loadCreditmemo();

        Logger::getInstance()->addMessageForLevel('creditmemo_info', 'Credit Memo information was changed');

        $this->updateIncrementId();
        $this->updateCreatedAt();
        $this->updateStatus();

        $this->creditmemo->setData('iwd_disable_after_save_event', true);

        $this->creditmemoRepository->saveWithoutAfterEvent($this->creditmemo);
    }

    /**
     * @return void
     */
    public function updateIncrementId()
    {
        $incrementId = $this->getIncrementId();
        Logger::getInstance()->addChange(
            'Increment Id',
            $this->creditmemo->getIncrementId(),
            $incrementId,
            'creditmemo_info'
        );
        $this->creditmemo->setIncrementId($incrementId);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getIncrementId()
    {
        $incrementId = $this->getCreditMemoData('increment_id');
        $incrementId = trim($incrementId);

        if ($this->creditmemo->getIncrementId() == $incrementId) {
            return $incrementId;
        }

        if (empty($incrementId)) {
            throw new LocalizedException(__("Credit Memo number is empty"));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $collection = $this->creditmemoRepository->getList($searchCriteria);

        if ($collection->getTotalCount() > 0) {
            throw new LocalizedException(__("Credit Memo number #%1 is already exists", $incrementId));
        }

        return $incrementId;
    }

    /**
     * @return void
     */
    public function updateCreatedAt()
    {
        $createdAt = $this->getCreditMemoData('created_at');
        Logger::getInstance()->addChange(
            'Created At',
            $this->creditmemo->getCreatedAt(),
            $createdAt,
            'creditmemo_info'
        );
        $this->creditmemo->setCreatedAt($createdAt);
    }

    /**
     * @return void
     */
    public function updateStatus()
    {
        $status = $this->getCreditMemoData('status');
        $statuses = $this->getStatusList();
        $old = $statuses[$this->creditmemo->getState()];
        $new = $statuses[$status];
        Logger::getInstance()->addChange('Status', $old, $new, 'creditmemo_info');
        $this->creditmemo->setState($status);
    }

    /**
     * @return string[]
     */
    public function getStatusList()
    {
        return $this->creditmemoRepository->create()->getStates();
    }

    /**
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     * @throws \Exception
     */
    protected function loadCreditmemo()
    {
        if ($this->creditmemo == null) {
            $creditmemoId = $this->getCreditmemoId();
            $this->creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if (!$this->creditmemo->getEntityId()) {
                throw new LocalizedException(__('Can not load credit memo with id ' . $creditmemoId));
            }
        }

        return $this->creditmemo;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getCreditmemoId()
    {
        $id = $this->getRequest()->getParam('creditmemo_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param creditmemo_id'));
        }

        return $id;
    }

    /**
     * @param bool|string $id
     * @return array|string
     * @throws \Exception
     */
    protected function getCreditMemoData($id=false)
    {
        $data = $this->getRequest()->getParam('creditmemo_info', []);

        if (empty($id)) {
            return $data;
        } elseif (isset($data[$id]) && !empty($data[$id])) {
            return $data[$id];
        }

        throw new LocalizedException(__('Empty param creditmemo_info[' . $id . ']'));
    }
}
