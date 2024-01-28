<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Data;

interface ProviderConfigInterface
{
    /**
     * String constants for property names
     */
    public const CLIENT_ID = "client_id";
    public const CLIENT_SECRET = "client_secret";
    public const REDIRECT_URI = "redirect_uri";

    /**
     * Getter for ClientId.
     *
     * @return string
     */
    public function getClientId(): string;

    /**
     * Getter for ClientSecret.
     *
     * @return string|null
     */
    public function getClientSecret(): ?string;

    /**
     * Getter for RedirectUri.
     *
     * @return string|null
     */
    public function getRedirectUri(): ?string;

    /**
     * Setter for ClientId.
     *
     * @param string $clientId
     *
     * @return static
     */
    public function setClientId(string $clientId): static;

    /**
     * Setter for ClientSecret.
     *
     * @param string|null $clientSecret
     *
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static;

    /**
     * Setter for RedirectUri.
     *
     * @param string|null $redirectUri
     *
     * @return static
     */
    public function setRedirectUri(?string $redirectUri): static;
}
