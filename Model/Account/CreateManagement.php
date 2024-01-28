<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\CreateManagementInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\State\InputMismatchException;

class CreateManagement implements CreateManagementInterface
{
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \BenJohnsonDev\SocialLogin\Model\Account\RandomPasswordGenerator $randomPasswordGenerator
     */
    public function __construct(
        protected Session $customerSession,
        protected CustomerInterfaceFactory $customerFactory,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilder,
        protected CustomerRepositoryInterface $customerRepository,
        protected CustomerRegistry $customerRegistry,
        protected AccountManagementInterface $accountManagement,
        protected RandomPasswordGenerator $randomPasswordGenerator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createAccount(
        ResourceOwnerInterface $user,
        AccessTokenInterface $accessToken,
        string $redirectUrl = ''
    ): CustomerInterface {
        $customer = $this->customerFactory->create();

        // Magento 2 only supports creating an account with an email as a unique identifier.
        // Future versions of this module get the users email from a credentials route after the "create" route.
        // Firstname and Lastname are optional, but if they are not provided we should throw an exception.
        if (!$user->getEmail() &&
            !$user->getFirstName() &&
            !$user->getLastName()
        ) {
            throw new InputMismatchException(
                __('"Email", "first name" and "last name" is required to create an account.')
            );
        }

        $customer->setData('firstname', $user->getFirstName())
            ->setData('lastname', $user->getLastName())
            ->setData('email', $user->getEmail())
            ->setCustomAttribute('provider', $this->customerSession->getData('provider'))
            ->setCustomAttribute('social_uid', $user->getId());
        $customer = $this->refreshToken($customer, $accessToken);

        // Unset the provider and state from the session - we shouldn't need this data anymore.
        $this->customerSession->unsetData([
            'provider',
            'state',
        ]);

        $password = $this->accountManagement->getPasswordHash(
            $this->getRandomPassword()
        );

        try {
            // Redirect uri is for confirmation email.
            return $this->accountManagement->createAccount($customer, $password, $redirectUrl);
        } catch (InputMismatchException) {

            // If User already exists with specified email then:
            // Update the users password and tokens and return the existing customer.
            $customer = $this->customerRepository->get($user->getEmail());
            $rpToken = $this->getResetPasswordToken($customer);
            $password = $this->getRandomPassword();
            $this->accountManagement->resetPassword($customer->getEmail(), $rpToken, $password);

            $customer = $this->refreshToken($customer, $accessToken);
            $this->customerRepository->save($customer);

            return $customer;
        }
    }

    /**
     * Set customer token
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function refreshToken(CustomerInterface $customer, AccessTokenInterface $accessToken): CustomerInterface
    {
        $customer
            ->setCustomAttribute('refresh_token', $accessToken->getRefreshToken())
            ->setCustomAttribute('token_expire', $accessToken->getExpires());
        return $customer;
    }

    /**
     * Get a random password.
     *
     * We should be asking the user to log in with the social provider, so this password is essentially useless...
     * Except as an entrypoint for brute force attacks.
     * It should be random, not used for anything and reset every time the user is logged in via social provider.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRandomPassword(): string
    {
        return $this->randomPasswordGenerator->generate();
    }

    /**
     * Get reset password token
     *
     * @param CustomerInterface $customer
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getResetPasswordToken(CustomerInterface $customer): string
    {
        $this->accountManagement->initiatePasswordReset(
            $customer->getEmail(),
            AccountManagement::EMAIL_RESET,
            1
        );

        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        return $customerSecure->getRpToken();
    }
}
