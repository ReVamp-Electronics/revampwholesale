<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;
use Aheadworks\Helpdesk\Model\TicketFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Ticket as TicketResource;
use Aheadworks\Helpdesk\Model\Source\Ticket\Status;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage as ThreadMessageResource;
use Aheadworks\Helpdesk\Model\ThreadMessageFactory;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;
use Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface;

/**
 * Submit reply
 * @package Aheadworks\Helpdesk\Controller\Ticket
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Reply extends \Aheadworks\Helpdesk\Controller\Ticket
{
    /**
     * Form key validator
     *
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * Ticket repository model (by default)
     *
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * Thread message factory
     *
     * @var ThreadMessageFactory
     */
    private $threadMessageFactory;

    /**
     * Thread message resource
     *
     * @var ThreadMessageResource
     */
    private $threadMessageResource;

    /**
     * TicketFlat repository model (by default)
     *
     * @var TicketFlatRepositoryInterface
     */
    private $ticketFlatRepository;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param TicketFactory $ticketModelFactory
     * @param TicketResource $ticketResource
     * @param FormKeyValidator $formKeyValidator
     * @param ThreadMessageResource $threadMessageResource
     * @param ThreadMessageFactory $threadMessageFactory
     * @param TicketRepositoryInterface $ticketRepository
     * @param TicketFlatRepositoryInterface $ticketFlatRepository
     * @param ForwardFactory $resultForwardFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ExternalKeyEncryptor $externalKeyEncryptor,
        TicketFactory $ticketModelFactory,
        TicketResource $ticketResource,
        FormKeyValidator $formKeyValidator,
        ThreadMessageResource $threadMessageResource,
        ThreadMessageFactory $threadMessageFactory,
        TicketRepositoryInterface $ticketRepository,
        TicketFlatRepositoryInterface $ticketFlatRepository,
        ForwardFactory $resultForwardFactory
    ) {
        $this->threadMessageFactory = $threadMessageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->threadMessageResource = $threadMessageResource;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $customerSession, $externalKeyEncryptor, $ticketModelFactory, $ticketResource);
    }

    /**
     * Reply action
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            try {
                $externalKey = null;
                if (isset($data['external_key'])) {
                    $externalKey = $data['external_key'];
                    $ticketId = $this->externalKeyEncryptor->getTicketId($externalKey);
                    if (!$ticketId) {
                        throw new \Exception(__('This ticket no longer exists.'));
                    }
                    $data['ticket_id'] = $ticketId;
                } else {
                    $ticketId = $data['ticket_id'];
                }

                $ticketModel = $this->ticketRepository->getById($ticketId);

                if (
                    $ticketModel->getId()
                    && (
                        $this->isCustomerValid($ticketModel->getCustomerId(), $ticketModel->getCustomerEmail())
                        || $this->isExternalKeyValid($externalKey, $ticketModel->getId())
                    )
                ) {
                    //update ticket
                    foreach ($data as $key => $item) {
                        $ticketModel->setData($key, $item);
                    }
                    $ticketModel->setStatus(Status::OPEN_VALUE);
                    $this->ticketRepository->save($ticketModel);

                    $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE;
                    $data['author_name'] = $ticketModel->getCustomerName();
                    $data['author_email'] = $ticketModel->getCustomerEmail();

                    if (isset($data['content']) && $data['content']) {
                        $threadMessage = $this->threadMessageFactory->create()
                            ->setData($data)
                        ;
                        $this->threadMessageResource->save($threadMessage);
                    }

                    //update ticket flat
                    $ticketFlat = $this->ticketFlatRepository->getByTicketId($ticketModel->getId());
                    $this->ticketFlatRepository->save($ticketFlat);

                    $this->messageManager->addSuccessMessage(__('Reply successfully added.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while adding the reply.'));
            }
            if ($this->customerSession->authenticate()) {
                $path = '*/*/view';
                $resultRedirect->setPath($path, ['id' => $ticketId]);
            } else {
                $path = '*/*/external';
                $resultRedirect->setPath($path, ['key' => $externalKey]);
            }
            return $resultRedirect;
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate form
     * @return bool
     */
    protected function validateFormKey()
    {
        return $this->formKeyValidator->validate($this->getRequest());
    }
}
