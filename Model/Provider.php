<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderConfigInterfaceFactory;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider as ProviderResourceModel;
use BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\Collection;
use League\OAuth2\Client\Provider\AbstractProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Provider extends AbstractModel implements ProviderInterface
{
    private const ENABLED = 'enabled';
    /**
     * @var string
     */
    protected $_eventPrefix = 'social_login_providers';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider $resource
     * @param \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\Collection $resourceCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderConfigInterfaceFactory $providerConfigFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProviderResourceModel $resource,
        Collection $resourceCollection,
        protected ScopeConfigInterface $scopeConfig,
        protected ProviderConfigInterfaceFactory $providerConfigFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model Constructor
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ProviderResourceModel::class);
    }

    /**
     * Get provider enabled
     */
    public function getEnabled(): ?string
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getIcon(): ?string
    {
        return $this->getData(self::ICON);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData('entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function getOauthClass($configOverload = null): ?AbstractProvider
    {
        $oauthClass = $this->getData(self::OAUTH_CLASS);

        $config = $this->providerConfigFactory->create([
            'data' => $this->getDefaultConfig() ?? [],
            'code' => $this->getCode(),
        ]);

        $data = array_merge($config->getData(), $configOverload ?? []);

        return new $oauthClass($data);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultConfig(): ?array
    {
        $config = $this->getData(self::DEFAULT_CONFIG) ?? '';
        return json_decode($config, true);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function getScope(): ?string
    {
        return $this->getData(self::SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): static
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultConfig(array $defaultConfig): static
    {
        $defaultConfig = json_encode($defaultConfig);
        return $this->setData(self::DEFAULT_CONFIG, $defaultConfig);
    }

    /**
     * Set provider to enabled
     *
     * @param bool $enabled
     * @return void
     */
    private function setEnabled(bool $enabled)
    {
        $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @inheritDoc
     */
    public function setIcon(string $icon): static
    {
        return $this->setData(self::ICON, $icon);
    }

    /**
     * @inheritDoc
     */
    public function setId(mixed $value): static
    {
        return $this->setData('entity_id', $value);
    }

    /**
     * @inheritDoc
     */
    public function setLabel(string $label): static
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @inheritDoc
     */
    public function setOauthClass(string $oauthClass): static
    {
        return $this->setData(self::OAUTH_CLASS, $oauthClass);
    }

    /**
     * @inheritDoc
     */
    public function setScope(string $scope): static
    {
        return $this->setData(self::SCOPE, $scope);
    }
}
