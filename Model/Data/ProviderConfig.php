<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Data;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderConfigInterface;
use BenJohnsonDev\SocialLogin\Controller\Account\Create;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class ProviderConfig extends DataObject implements ProviderConfigInterface
{
    public const SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH = 'social_login/%s/%s';
    public const SCOPE = 'scope';

    /**
     * @param string $code
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $extra Provider-specific default config (e.g. graphApiVersion for Facebook).
     * @param array $data
     */
    public function __construct(
        protected string $code,
        protected ScopeConfigInterface $scopeConfig,
        protected UrlInterface $urlBuilder,
        protected array $extra = [],
        array $data = [],
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function toOauthConfig(array $extra = []): array
    {
        $config = [
            'clientId'     => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri'  => $this->getRedirectUri(),
        ];

        $scopeValue = $this->scopeConfig->getValue(
            sprintf(self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH, $this->code, self::SCOPE),
            ScopeInterface::SCOPE_STORE
        );
        if ($scopeValue !== null && $scopeValue !== '') {
            $config['scopes'] = array_map('trim', explode(',', $scopeValue));
        }

        return array_merge($this->extra, $extra, $config);
    }

    /**
     * @inheritDoc
     */
    public function getClientId(): string
    {
        return $this->getData(self::CLIENT_ID) ?? $this->scopeConfig->getValue(
            sprintf(self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH, $this->code, self::CLIENT_ID),
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getClientSecret(): ?string
    {
        return $this->getData(self::CLIENT_SECRET) ?? $this->scopeConfig->getValue(
            sprintf(self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH, $this->code, self::CLIENT_SECRET),
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUri(): ?string
    {
        $override = $this->getData(self::REDIRECT_URI) ?? $this->scopeConfig->getValue(
            sprintf(self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH, $this->code, self::REDIRECT_URI),
            ScopeInterface::SCOPE_STORE
        );

        return $override ?: $this->urlBuilder->getUrl(Create::ROUTE, ['_secure' => true]);
    }

    /**
     * @inheritDoc
     */
    public function setClientId(string $clientId): static
    {
        return $this->setData(self::CLIENT_ID, $clientId);
    }

    /**
     * @inheritDoc
     */
    public function setClientSecret(?string $clientSecret): static
    {
        return $this->setData(self::CLIENT_SECRET, $clientSecret);
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUri(?string $redirectUri): static
    {
        return $this->setData(self::REDIRECT_URI, $redirectUri);
    }
}
