<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use VPG\VodaPayGateway\Gateway\Http\Client\Client;
use VPG\VodaPayGateway\Gateway\Config\Config;
use Magento\Framework\App\Helper\Context;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'vodapay_gateway';

    protected $_gatewayConfig;

    public function __construct(
    Config $gatewayConfig,
    Context $context,
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        //$this->_urlBuilder = $context->getUrlBuilder();
    }
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'api_key' => $this->_gatewayConfig->getApiKey(),
                    "endpoint" => $this->_gatewayConfig->getEndpointUrl(),
                    "environment" => $this->_gatewayConfig->getEnvironment(), 
                ]
            ]
        ];
    }
}
