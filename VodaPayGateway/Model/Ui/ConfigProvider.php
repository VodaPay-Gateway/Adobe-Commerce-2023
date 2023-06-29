<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use VPG\VodaPayGateway\Gateway\Http\Client\Client;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'vodapay_gateway';

    protected $_gatewayConfig;
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
    protected $request;
    protected $_assetRepo;

    public function __construct(
    Config $gatewayConfig,
    Context $context,
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_urlBuilder = $context->getUrlBuilder();
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
                    "endpoint" => $this->_gatewayConfig->getEndpoint(),
                    "environment" => $this->_gatewayConfig->getEnvironment(), 
                ]
            ]
        ];
    }
}
