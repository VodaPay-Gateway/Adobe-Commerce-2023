<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Gateway\Config;

/**
 * Class Config.
 * Values returned from Magento\Payment\Gateway\Config\Config.getValue()
 * are taken by default from ScopeInterface::SCOPE_STORE
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'vodapay_gateway';

    const ACTIVE = 'active';
    const TITLE = 'title';
    const GATEWAY_LOGO = 'gateway_logo';
    const MERCHANT_Api_Key = 'api_key';
    const GATEWAY_URL = 'gateway_url';
    const ENVIRONMENT = 'environment';
    const DEBUG = 'debug';
    const STORE_URL = 'store_url';
    const STATUS = 'vodapay_approved_order_status';

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getApiKey() {
        return $this->getValue(self::MERCHANT_Api_Key);
    }

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getTitle() {
        return $this->getValue(self::TITLE);
    }

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getStoreUrl() {
        return $this->getValue(self::STORE_URL);
    }

    /**
     * Get Logo
     *
     * @return string
     */
    public function getLogo() {
        return $this->getValue(self::GATEWAY_LOGO);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getApprovedStatus() {
        return $this->getValue(self::STATUS);
    }


	/**
	 * get the endpoint gateway Url
	 * @return string
	 */
	public function getEndpointUrl() {
        if(($this->getValue(self::ENVIRONMENT)  == '0') || ($this->getValue(self::ENVIRONMENT)  == '1'))
        {
            return 'https://api.vodapaygatewayuat.vodacom.co.za';
        }

        return 'https://api.vodapaygatewayuat.vodacom.co.za';
	}

    /**
     * Get API Key
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getValue(self::ENVIRONMENT);
    }

    /**
     * Get Payment configuration status
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }
}
