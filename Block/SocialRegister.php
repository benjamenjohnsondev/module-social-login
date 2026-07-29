<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Block;

use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class SocialRegister extends Template
{
    public const SOCIAL_LOGIN_GENERAL_FA_CONFIG_PATH = 'social_login/general/fa';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface $providerRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        protected ProviderRepositoryInterface $providerRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get FontAwesome Kit ID
     *
     * This is a workaround - currently the FontAwesome Kit CDN doesn't work with requirejs properly...
     * But at least this works.
     *
     * @return string
     */
    public function getFaKitId(): string
    {
        return $this->_scopeConfig->getValue(
            self::SOCIAL_LOGIN_GENERAL_FA_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * Get enabled providers from store config
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providerRepository->getEnabledProviders();
    }

    /**
     * Check if newsletter is enabled
     *
     * @return bool
     */
    public function isNewsletterEnabled(): bool
    {
        return $this->_scopeConfig->isSetFlag(
            'newsletter/general/active',
            ScopeInterface::SCOPE_STORE
        );
    }
}
