<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\LoginManagementInterface;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;

class LoginManagement implements LoginManagementInterface
{
    public const ACCESS_TOKEN_EXPIRED_ERROR_MESSAGE = 'Your access token has expired. Please try again.';

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        protected Session $customerSession,
        protected RedirectFactory $redirectFactory,
        protected UrlInterface $urlBuilder,
        protected MessageManager $messageManager,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function loginCustomer(
        CustomerInterface $customer,
        AccessTokenInterface $accessToken,
        ProviderInterface $provider
    ): ResultInterface {
        if ($accessToken->getExpires() < time()) {
            throw new InputMismatchException(__(self::ACCESS_TOKEN_EXPIRED_ERROR_MESSAGE));
        }

        $providerAttribute = $customer->getCustomAttribute('provider');
        if ($providerAttribute === null || $providerAttribute->getValue() !== $provider->getCode()) {
            throw new InputMismatchException(__('The customer is not associated with the provider.'));
        }

        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->customerSession->regenerateId();
        $this->messageManager->addSuccessMessage(__('You logged in successfully.'));

        // @TODO Get redirect URL - this may need to be set on the session at the action controller
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath($this->urlBuilder->getUrl('customer/account', ['_secure' => true]));

        return $resultRedirect;
    }
}
