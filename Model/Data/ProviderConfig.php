<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Data;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderConfigInterface;
use BenJohnsonDev\SocialLogin\Controller\Account\Create;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class ProviderConfig extends DataObject implements ProviderConfigInterface
{
    public const SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH = 'social_login/%s/%s';

    /**
     * @param string $code
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        protected string $code,
        protected ScopeConfigInterface $scopeConfig,
        protected UrlInterface $urlBuilder,
        protected Session $customerSession,
        protected array $data = [],
    ) {
        parent::__construct($data);
    }

    /**
     * Modify getData to return formatted config array.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null): mixed
    {
        if ($key !== '') {
            return parent::getData($key, $index);
        }

        return [
            ...$this->data,
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri' => $this->getRedirectUri(),
        ];
    }

    /**
     * Getter for ClientId.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->scopeConfig->getValue(
            sprintf(
                self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH,
                $this->getCode(),
                self::CLIENT_ID
            ),
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * Getter for code.
     *
     * @return string
     */
    private function getCode(): string
    {
        return $this->code;
    }

    /**
     * Getter for ClientSecret.
     *
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->scopeConfig->getValue(
            sprintf(
                self::SOCIAL_LOGIN_CLIENT_KEYS_CONFIG_PATH,
                $this->getCode(),
                self::CLIENT_SECRET
            ),
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * Getter for RedirectUri.
     *
     * @return string|null
     */
    public function getRedirectUri(): ?string
    {
        return $this->urlBuilder->getUrl(
            Create::ROUTE,
            ['_secure' => true]
        );
    }

    /**
     * Setter for ClientId.
     *
     * @param string $clientId
     *
     * @return static
     */
    public function setClientId(string $clientId): static
    {
        return $this->setData(self::CLIENT_ID, $clientId);
    }

    /**
     * Setter for ClientSecret.
     *
     * @param string|null $clientSecret
     *
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static
    {
        return $this->setData(self::CLIENT_SECRET, $clientSecret);
    }

    /**
     * Setter for RedirectUri.
     *
     * @param string|null $redirectUri
     *
     * @return static
     */
    public function setRedirectUri(?string $redirectUri): static
    {
        return $this->setData(self::REDIRECT_URI, $redirectUri);
    }
}
