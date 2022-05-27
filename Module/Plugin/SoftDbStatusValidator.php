<?php

namespace MageSuite\SoftDbStatusValidation\Module\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;

class SoftDbStatusValidator extends \Magento\Framework\Module\Plugin\DbStatusValidator
{
    /**
     * @var \MageSuite\SoftDbStatusValidation\Model\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Module\DbVersionInfo $dbVersionInfo,
        \MageSuite\SoftDbStatusValidation\Model\Config $config,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($cache, $dbVersionInfo, $deploymentConfig);

        $this->config = $config;
        $this->logger = $logger;
        $this->appState = $appState;
    }

    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        try {
            parent::beforeDispatch($subject, $request);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            if ($this->config->isEnabled() && $this->appState->getMode() === \Magento\Framework\App\State::MODE_PRODUCTION) {
                $this->logger->critical($exception->getMessage());
            } else {
                throw $exception;
            }
        }
    }
}
