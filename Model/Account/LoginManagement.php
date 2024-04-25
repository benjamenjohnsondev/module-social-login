<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\LoginManagementInterface;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;

class LoginManagement implements LoginManagementInterface
{
    public const ACCESS_TOKEN_EXPIRED_ERROR_MESSAGE = 'Your access token has expired. Please try again.';

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        protected Session $customerSession,
        protected CustomerRegistry $customerRegistry,
        protected RedirectFactory $redirectFactory,
        protected UrlInterface $urlBuilder,
        protected MessageManager $messageManager
    ) {
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function loginCustomer(
        CustomerInterface $customer,
        AccessTokenInterface $accessToken,
        ProviderInterface $provider
    ): Redirect {
        if ($accessToken->getExpires() < time()) {
            $error = self::ACCESS_TOKEN_EXPIRED_ERROR_MESSAGE;
            throw new InputMismatchException(__("$error"));
        }

        if ($customer->getCustomAttribute('provider')->getValue() !== $provider->getCode()) {
            $error = 'The customer is not associated with the provider.';
            throw new InputMismatchException(__("$error"));
        }

        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->customerSession->regenerateId();
        $this->messageManager->addSuccessMessage(__('You logged in sucessfully.'));

        // @TODO Get redirect URL - this may need to be set on the session at the action controller
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath($this->urlBuilder->getUrl('customer/account', ['_secure' => true]));

        return $resultRedirect;
    }
}
