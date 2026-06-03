<?php

declare(strict_types=1);

namespace MageSuite\SoftDbStatusValidation\Helper;

class Configuration
{
    protected const ENABLED_PATH = 'soft_db_status_validation/enabled';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    ) {}

    public function isEnabled(): bool
    {
        return  $this->scopeConfig->isSetFlag(self::ENABLED_PATH);
    }
}
