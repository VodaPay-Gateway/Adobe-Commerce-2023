<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Checkout\Model\Session;
use VPG\VodaPayGateway\Gateway\Config\Config;

class RefundRequest implements BuilderInterface
{
    private $_session;
    private $_gatewayConfig;

    /**
     * @param Config $gatewayConfig
     * @param Session $session
     */
    public function __construct(
        Config $gatewayConfig,
        Session $session
    ) {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_session = $session;
    }

    /**
     * Builds ENV request
     * From: https://github.com/magento/magento2/blob/2.1.3/app/code/Magento/Payment/Model/Method/Adapter.php
     * The $buildSubject contains:
     * 'payment' => $this->getInfoInstance()
     * 'paymentAction' => $paymentAction
     * 'stateObject' => $stateObject
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject) {
    	$gateway_api_key = $this->_gatewayConfig->getApiKey();
    	$gateway_refund_gateway_url = $this->_gatewayConfig->getEndpointUrl().'/V2/Pay/Refund';
        $env = $this->_gatewayConfig->getEnvironment();
    	return [ 
            'API_KEY'=>$gateway_api_key, 
            'REFUND_URL'=>$gateway_refund_gateway_url,
            'ENV' => $env,
            'URL' => $this->_gatewayConfig->getEndpointUrl(),
            'NOTIFICATION_URL' => $this->_gatewayConfig->getNotificationUrl()
        ];
    }
}
