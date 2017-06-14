<?php
namespace Aheadworks\SocialLogin\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomerConvertException
 */
class CustomerConvertException extends LocalizedException
{
    /**
     * @var
     */
    private $errors;

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param array $errors
     * @param \Exception|null $cause
     */
    public function __construct(
        \Magento\Framework\Phrase $phrase,
        array $errors,
        \Exception $cause = null
    ) {
        parent::__construct($phrase, $cause);
        $this->errors = $errors;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
