<?php

namespace MageSuite\SoftDbStatusValidation\Test\Unit\Module\Plugin;

class SoftDbStatusValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testThatExceptionAreCaughtAndErrorLogged()
    {
        $cacheMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $cacheMock
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(false)
        ;

        $dbVersionMock = $this->createMock(\Magento\Framework\Module\DbVersionInfo::class);
        $dbVersionMock
            ->method('getDbVersionErrors')
            ->willReturn([
                [
                    \Magento\Framework\Module\DbVersionInfo::KEY_CURRENT => '0.5.0',
                    \Magento\Framework\Module\DbVersionInfo::KEY_REQUIRED => '1.0.0',
                    \Magento\Framework\Module\DbVersionInfo::KEY_MODULE => 'DummyModule',
                    \Magento\Framework\Module\DbVersionInfo::KEY_TYPE => 'schema'
                ]
            ])
        ;

        $configMock = $this->createMock(\MageSuite\SoftDbStatusValidation\Helper\Configuration::class);

        $configMock
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('Please upgrade your database'))
        ;

        $appStateMock = $this->createMock(\Magento\Framework\App\State::class);
        $appStateMock
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION)
        ;

        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock
            ->method('get')
            ->willReturn(false);
        
        $validator = new \MageSuite\SoftDbStatusValidation\Plugin\Magento\Framework\App\FrontController\SoftDbStatusValidator(
            $cacheMock,
            $dbVersionMock,
            $deploymentConfigMock,
            $configMock,
            $appStateMock,
            $loggerMock
        );

        $validator->beforeDispatch(
            $this->createMock(\Magento\Framework\App\FrontController::class),
            $this->createMock(\Magento\Framework\App\RequestInterface::class)
        );
    }
}
