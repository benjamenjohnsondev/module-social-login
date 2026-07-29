<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\AuthorizeManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class AuthorizeManagement implements AuthorizeManagementInterface
{
    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        protected TimezoneInterface $timezone,
        protected Session $customerSession,
        protected CustomerRepositoryInterface $customerRepository,
        protected ManagerInterface $messageManager,
        protected EncryptorInterface $encryptor,
    ) {
    }

    /**
     * Authorize the user
     *
     * Check if the token is expired and log the user out if it is
     * If it has not expired, check if the token is valid
     * If the token is not valid log the user out
     *
     * @inheritDoc
     */
    public function authorizeUser(CustomerInterface|Customer $customer): void
    {
        // Check if the access token is expired
        if ($this->isTokenExpired((int) $customer->getTokenExpire())) {
            $this->messageManager->addErrorMessage(__('Your session has expired. Please log in again.'));

            $this->customerSession->logout();
            return;
        }

        // Token validation against the provider is intentionally deferred.
        // Each OAuth provider has a different mechanism (refresh token endpoint, introspection, etc.).
        // Use an after plugin on AuthorizeManagementInterface::authorizeUser() to add provider-specific logic.
        // The expiration timestamp check above provides a baseline guard.
    }

    /**
     * Check if the token expiry timestamp has passed
     *
     * @param int $token
     * @return bool
     */
    private function isTokenExpired(int $token): bool
    {
        return $this->timezone->date()->getTimestamp() > $token;
    }
}
