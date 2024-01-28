<?php

declare (strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterface;
use Magento\Framework\Api\SearchResults;

class ProviderSearchResult extends SearchResults implements ProviderSearchResultInterface
{
}
