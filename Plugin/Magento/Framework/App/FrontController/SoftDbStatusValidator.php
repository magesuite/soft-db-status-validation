<?php

declare(strict_types=1);

namespace MageSuite\SoftDbStatusValidation\Plugin\Magento\Framework\App\FrontController;

class SoftDbStatusValidator extends \Magento\Framework\Module\Plugin\DbStatusValidator
{
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Module\DbVersionInfo $dbVersionInfo,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        protected \MageSuite\SoftDbStatusValidation\Helper\Configuration $configuration,
        protected \Magento\Framework\App\State $appState,
        protected \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($cache, $dbVersionInfo, $deploymentConfig);
    }

    public function beforeDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Magento\Framework\App\RequestInterface $request
    ): void {
        try {
            parent::beforeDispatch($subject, $request);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            if ($this->configuration->isEnabled() && $this->appState->getMode() === \Magento\Framework\App\State::MODE_PRODUCTION) {
                $this->logger->critical($exception->getMessage());
            } else {
                throw $exception;
            }
        }
    }
}
