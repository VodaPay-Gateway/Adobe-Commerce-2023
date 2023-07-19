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
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Backend\Model\Session\Quote;

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
    Repository $assetRepo
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_assetRepo = $assetRepo;

        //$this->_urlBuilder = $context->getUrlBuilder();
    }
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        /** @var $om \Magento\Framework\ObjectManagerInterface */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $om->get('Magento\Framework\App\RequestInterface');
        $params = array();
        $params = array_merge(['_secure' => $request->isSecure()], $params);

        $logo = $this->_assetRepo->getUrlWithParams('VPG_VodaPayGateway::images/vpg.svg', $params);

        return [
            'payment' => [
                self::CODE => [
                    'api_key' => $this->_gatewayConfig->getApiKey(),
                    "endpoint" => $this->_gatewayConfig->getEndpointUrl(),
                    "environment" => $this->_gatewayConfig->getEnvironment(), 
                    "logo" => $logo
                ]
            ]
        ];
    }
}
