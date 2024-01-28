<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Controller\Account;

use BenJohnsonDev\SocialLogin\Api\Account\CreateManagementInterface;
use BenJohnsonDev\SocialLogin\Api\Account\LoginManagementInterface;
use BenJohnsonDev\SocialLogin\Model\ProviderRepository;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class Create implements HttpGetActionInterface, CsrfAwareActionInterface
{
    public const ROUTE = 'social/account/create';
    public const INVALID_FORM_KEY_MESSAGE = 'Invalid Form Key. Please refresh the page.';
    public const PROVIDER_NOT_FOUND_MESSAGE = 'There was an error retrieving the provider - original error message: %s';
    public const GET_ACCESS_TOKEN_MESSAGE = 'There was an error getting the access token - original error message: %s';

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \BenJohnsonDev\SocialLogin\Model\ProviderRepository $providerRepository
     * @param \BenJohnsonDev\SocialLogin\Api\Account\CreateManagementInterface $createManagement
     * @param \BenJohnsonDev\SocialLogin\Api\Account\LoginManagementInterface $loginManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected RequestInterface $request,
        protected RedirectFactory $redirectFactory,
        protected UrlInterface $urlBuilder,
        protected Session $customerSession,
        protected MessageManager $messageManager,
        protected FormKey $formKey,
        protected ProviderRepository $providerRepository,
        protected CreateManagementInterface $createManagement,
        protected LoginManagementInterface $loginManagement,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     *
     * @return ResultInterface|RedirectInterface
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function execute()
    {
        $state = $this->getRequest()->getParam('state');

        // CSRF check - if state is null or state does not match session state.
        if ($state === null ||
            $state === '' ||
            $state !== $this->customerSession->getData('state')
        ) {
            return $this->createError(self::INVALID_FORM_KEY_MESSAGE);
        }

        // Get access token.
        try {
            [$provider, $oauth, $accessToken] = $this->extractOauth();
        } catch (NoSuchEntityException $e) {
            // Caught if provider does not exist.
            $this->logger->error(sprintf(
                self::PROVIDER_NOT_FOUND_MESSAGE,
                $e->getMessage()
            ));
            return $this->createError();
        } catch (IdentityProviderException $e) {
            // Caught if access token cannot be retrieved.
            $this->logger->error(sprintf(
                self::GET_ACCESS_TOKEN_MESSAGE,
                $e->getMessage()
            ));
            return $this->createError();
        }

        $user = $oauth->getResourceOwner($accessToken);

        // Create the account.
        try {
            $customer = $this->createManagement->createAccount($user, $accessToken);
        } catch (InputException $e) {
            // If bad input is provided - this shouldn't happen at this point - log it anyway.
            $this->logger->error(sprintf(
                'There was an error creating the account - original error message: %s',
                $e->getMessage()
            ));
            return $this->createError();
        } catch (NoSuchEntityException $e) {
            // If the customer is not found - this shouldn't happen at this point - log it.
            $this->logger->error(sprintf(
                'There was an error creating the account - original error message: %s',
                $e->getMessage()
            ));
            return $this->createError();
        } catch (LocalizedException $e) {
            // For everything else - log it.
            $this->logger->error(sprintf(
                'There was an error creating the account - original error message: %s',
                $e->getMessage()
            ));
            return $this->createError();
        }

        // Log in the customer.
        try {
            return $this->loginManagement->loginCustomer($customer, $accessToken, $provider);
        } catch (InputMismatchException $e) {
            // If the customer is not associated with the provider - log it.
            $this->logger->error(sprintf(
                'There was an error logging in the customer - original error message: %s',
                $e->getMessage()
            ));
            return $this->createError();
        }
    }

    /**
     * Get request object.
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Create error redirect
     *
     * @param string|null $error
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function createError(string $error = null): Redirect
    {
        $resultRedirect = $this->redirectFactory->create();
        $url = $this->urlBuilder->getUrl('customer/account/create', ['_secure' => true]);
        $resultRedirect->setPath($url);

        $this->messageManager->addErrorMessage(__($error ?? 'An error occurred. Please try again.'));
        return $resultRedirect;
    }

    /**
     * Gets oauth details from request
     *
     * @return array{
     *     \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface,
     *     \League\OAuth2\Client\Provider\AbstractProvider,
     *     \League\OAuth2\Client\Token\AccessTokenInterface
     * }
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function extractOauth(): array
    {
        // Some providers, ahem Facebook, have buggy implementations of the redirect uri.
        // This is a workaround for that.
        // Otherwise, we would set the provider in the redirect uri and not need to use the session.
        $providerFromSession = $this->customerSession->getData('provider');

        $provider = $this->providerRepository->getByCode($providerFromSession);

        $oauth = $provider->getOauthClass();
        $accessToken = $oauth->getAccessToken('authorization_code', [
            'code' => $this->getRequest()->getParam('code'),
        ]);
        return [$provider, $oauth, $accessToken];
    }

    /**
     * Get new form key
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
