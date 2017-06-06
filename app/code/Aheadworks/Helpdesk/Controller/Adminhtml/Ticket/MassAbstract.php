<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;
use Aheadworks\Helpdesk\Api\Data\TicketInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassAbstract
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
abstract class MassAbstract extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Ticket Collection
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection
     */
    protected $collection;

    /**
     * Massaction filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * @var PermissionValidator
     */
    protected $permissionValidator;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection $collection
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection $collection,
        PermissionValidator $permissionValidator
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->collection = $collection;
        $this->filter = $filter;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * Mass update ticket(s) action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->collection = $this->filter->getCollection($this->collection);
            $paramCode = $this->getFilterParam();
            $paramValue = $this->getRequest()->getParam($paramCode);
            $count = 0;

            foreach ($this->collection->getItems() as $ticket) {
                try {
                    $ticketModel = $this->ticketRepository->getById($ticket->getId());
                    $ticketFlatModel = $this->ticketFlatRepository->getByTicketId($ticketModel->getId());
                } catch (\Exception $e) {
                    $ticketModel = null;
                    $ticketFlatModel = null;
                }
                if ($ticketModel && $ticketFlatModel) {
                    try {
                        foreach ($this->getRequiredPermissions() as $type => $failedMessage) {
                            if (!$this->permissionValidator->validate($type, $ticketModel)) {
                                throw new LocalizedException($failedMessage);
                            }
                        }

                        $ticketModel->setData($paramCode, $paramValue);

                        $this->additionalPermissionValidation($ticketModel);

                        $this->ticketRepository->save($ticketModel);
                        $ticketFlatModel->setData('order_id', $ticketModel->getOrderId());
                        $ticketFlatModel->setData('agent_id', $ticketModel->getAgentId());
                        $this->ticketFlatRepository->save($ticketFlatModel);
                        $count++;
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage('Something went wrong');
                    }
                }
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $count)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Something went wrong');
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }

    /**
     * Get filter param
     *
     * @return string
     */
    abstract protected function getFilterParam();

    /**
     * Get required permissions
     *
     * @return []
     */
    abstract protected function getRequiredPermissions();

    /**
     * Proceed additional permission validation
     *
     * @param TicketInterface $ticket
     * @return this
     * @throws LocalizedException
     */
    abstract protected function additionalPermissionValidation($ticket);
}
