<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Ticket;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class ExternalKeyEncryptor
 * @package Aheadworks\Helpdesk\Model\Ticket
 */
class ExternalKeyEncryptor
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt external key
     *
     * @param string $customerEmail
     * @param int $ticketId
     * @return string
     */
    public function encrypt($customerEmail, $ticketId)
    {
        return base64_encode($this->encryptor->encrypt($customerEmail . ',' . $ticketId));
    }

    /**
     * Decrypt external key
     *
     * @param string $key
     * @return string
     */
    public function decrypt($key)
    {
        return $this->encryptor->decrypt(base64_decode($key));
    }

    /**
     * Get ticket id from external key specified
     *
     * @param string $key
     * @return int|null
     */
    public function getTicketId($key)
    {
        try {
            $decryptedKey = $this->decrypt($key);
            list($email, $ticketId) = explode(',', $decryptedKey);
            if (!empty($email) && !empty($ticketId)) {
                return (int)$ticketId;
            }
        } catch (\Exception $e) {

        }
        return null;
    }

    /**
     * Get email from external key specified
     *
     * @param string $key
     * @return string|null
     */
    public function getEmail($key)
    {
        try {
            $decryptedKey = $this->decrypt($key);
            list($email, $ticketId) = explode(',', $decryptedKey);
            if (!empty($email) && !empty($ticketId)) {
                return $email;
            }
        } catch (\Exception $e) {

        }
        return null;
    }
}
