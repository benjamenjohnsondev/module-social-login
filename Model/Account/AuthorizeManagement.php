<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\AuthorizeManagementInterface;
use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use Exception;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class AuthorizeManagement implements AuthorizeManagementInterface
{
    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface $providerRepository
     */
    public function __construct(
        protected TimezoneInterface $timezone,
        protected Session $customerSession,
        protected CustomerRepositoryInterface $customerRepository,
        protected ManagerInterface $messageManager,
        protected ProviderRepositoryInterface $providerRepository
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
    public function authorizeUser($customer): void
    {
        // Check if the access token is expired
        if ($this->isTokenExpired((int) $customer->getTokenExpire())) {
            $this->messageManager->addErrorMessage(__('Your session has expired. Please log in again.'));

            $this->customerSession->logout();
            return;
        }

        // Unfortunately almost every oauth provider has a different way of checking if the token is valid
        // At this point you might want to validate the stored token with the provider
        // This could be via a refresh token or a request to the provider's API
        // For now we implicitly trust the expiration date
        // This may change in the future - we may need to save the access token and validate it with the provider
        // You can use an after plugin to add your own logic here

        // The below is just an example
        //if (!$this->isTokenValid($customer)) {
        //    $this->customerSession->logout();
        //    return;
        //}
    }

    /**
     * Check if the token is expired
     *
     * @param int $token
     * @return bool
     */
    private function isTokenExpired(int $token): bool
    {
        return $this->timezone->date()->getTimestamp() > $token;
    }

    /**
     * Check if token is valid
     *
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isTokenValid($customer)
    {
        $provider = $this->providerRepository->getByCode($customer->getProvider());
        $oauth = $provider->getOauthClass();
        $grant = new RefreshToken();

        try {
            $accessToken = $oauth->getAccessToken($grant, ['refresh_token' => $customer->getRefreshToken()]);
            $this->refreshToken($customer, $accessToken);
            return true;
        } catch (Exception $e) {
            // Token is not valid - log the user out
            return false;
        }
    }

    /**
     * Refresh the token
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer $customer
     * @param AccessTokenInterface $accessToken
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @noinspection PhpMissingParamTypeInspection
     */
    private function refreshToken($customer, AccessTokenInterface $accessToken): void
    {
        $customer
            ->setCustomAttribute('refresh_token', $accessToken->getRefreshToken())
            ->setCustomAttribute('token_expire', $accessToken->getExpires());
        $this->customerRepository->save($customer);
    }
}
