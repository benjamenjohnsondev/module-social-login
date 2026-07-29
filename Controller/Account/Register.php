<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Controller\Account;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class Register implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ROUTE = 'social/account/register';

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface $providerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        protected RequestInterface $request,
        protected Session $customerSession,
        protected UrlInterface $urlBuilder,
        protected ResultFactory $resultFactory,
        protected ProviderRepositoryInterface $providerRepository,
        protected ManagerInterface $messageManager,
    ) {
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException|\Magento\Framework\Exception\NoSuchEntityException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function execute()
    {
        $providerParam = $this->getRequest()->getPostValue('provider');

        try {
            $provider = $this->providerRepository->getByCode($providerParam);
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('The requested social login provider is not available.'));
            return $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT)
                ->setUrl($this->urlBuilder->getUrl('customer/account/login'));
        }

        $oauth = $provider->getOauthClass();
        $authorizationUrl = $oauth->getAuthorizationUrl();

        if ($oauth->getState() === null) {
            throw new ($this->createCsrfValidationException($this->getRequest()))();
        }
        
        // Persist state and initiation timestamp to session — used for CSRF prevention and TTL validation.
        $this->customerSession
            ->setData('provider', $provider->getCode())
            ->setData('is_subscribed', $this->getRequest()->getParam('is_subscribed'))
            ->setData('state', $oauth->getState())
            ->setData('state_initiated_at', time());

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setUrl($authorizationUrl);
    }

    /**
     * Get request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Get redirect uri
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return string
     */
    public function getRedirectUri(ProviderInterface $provider): string
    {
        return $this->urlBuilder->getUrl(
            Create::ROUTE,
            [
                '_query' => [
                    'provider' => $provider->getCode(),
                    'is_subscribed' => $this->getRequest()->getParam('is_subscribed'),
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
