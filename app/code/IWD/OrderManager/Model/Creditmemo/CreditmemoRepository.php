<?php

namespace IWD\OrderManager\Model\Creditmemo;

use Magento\Sales\Model\Order\CreditmemoRepository as MageCreditmemoRepository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use IWD\OrderManager\Model\ResourceModel\Creditmemo\Creditmemo as ResourceModelCreditmemo;

/**
 * Class CreditmemoRepository
 * @package IWD\OrderManager\Model\Creditmemo
 */
class CreditmemoRepository extends MageCreditmemoRepository
{
    /**
     * @var ResourceModelCreditmemo
     */
    private $creditmemo;

    /**
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param ResourceModelCreditmemo $creditmemo
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        ResourceModelCreditmemo $creditmemo
    ) {
        parent::__construct($metadata, $searchResultFactory);
        $this->creditmemo = $creditmemo;
    }

    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     * @throws CouldNotSaveException
     */
    public function saveWithoutAfterEvent(\Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        try {
            $this->creditmemo->disableAfterSaveEvent();
            $this->creditmemo->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save credit memo'), $e);
        }
        return $this->registry[$entity->getEntityId()];
    }
}
