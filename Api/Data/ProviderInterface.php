<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Data;

use League\OAuth2\Client\Provider\AbstractProvider;

interface ProviderInterface
{
    /**
     * String constants for property names
     */
    public const LABEL = 'label';
    public const SCOPE = 'scope';
    public const ICON = 'icon';
    public const OAUTH_CLASS = 'oauth_class';
    public const CODE = 'code';
    public const DEFAULT_CONFIG = 'default_config';

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Return Default config
     *
     * @return array|null
     */
    public function getDefaultConfig(): ?array;

    /**
     * Getter for Icon
     *
     * @return string|null
     */
    public function getIcon(): ?string;

    /**
     * Getter for Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Getter for Label
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Getter for Oauth class
     *
     * @param array|null $configOverload
     * @return \League\OAuth2\Client\Provider\AbstractProvider|null
     */
    public function getOauthClass(?array $configOverload): ?AbstractProvider;

    /**
     * Getter for Scope
     *
     * @return string|null
     */
    public function getScope(): ?string;

    /**
     * Setter for code
     *
     * @param string $code
     * @return static
     */
    public function setCode(string $code): static;

    /**
     * Return Default config
     *
     * @param array $defaultConfig
     * @return static
     */
    public function setDefaultConfig(array $defaultConfig): static;

    /**
     * Set Icon
     *
     * @param string $icon
     * @return static
     */
    public function setIcon(string $icon): static;

    /**
     * Set Id
     *
     * @param mixed $value
     * @return static
     */
    public function setId($value): static;

    /**
     * Set Label
     *
     * @param string $label
     * @return static
     */
    public function setLabel(string $label): static;

    /**
     * Set Oauth class
     *
     * @param string $oauthClass
     * @return static
     */
    public function setOauthClass(string $oauthClass): static;

    /**
     * Set Scope
     *
     * @param string $scope
     * @return static
     */
    public function setScope(string $scope): static;
}
