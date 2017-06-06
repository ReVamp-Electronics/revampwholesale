<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Mail;

use Aheadworks\Helpdesk\Model\Config;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Sender
 * @package Aheadworks\Helpdesk\Model\Mail
 */
class Sender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @param TransportBuilder $transportBuilder
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        MessageManagerInterface $messageManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
    }

    /**
     * Send email
     * @param $emailData
     * @param bool $needReplyTo
     */
    public function sendEmail($emailData, $needReplyTo = true)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($emailData['template_id'])
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $emailData['store_id']
            ])
            ->setTemplateVars($emailData)
            ->setFrom($emailData['from'])
            ->addTo($emailData['to'], $emailData['sender_name'])
        ;
        if (isset($emailData['cc_recipients'])) {
            $this->transportBuilder->addCc($emailData['cc_recipients']);
        }
        if ($needReplyTo) {
            $this->transportBuilder->setReplyTo($emailData['gateway']);
        }
        $transport = $this->transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            $this->messageManager->addErrorMessage($e->getMessage(), Config::EMAIL_ERROR_MESSAGE_GROUP);
        }
    }
}