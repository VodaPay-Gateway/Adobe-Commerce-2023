<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace VPG\VodaPayGateway\Gateway\Request;

// require_once( dirname(__FILE__) .'/Vpg/lib/Model/ModelInterface.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/VodaPayGatewayPayment.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/Notifications.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/Styling.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/ElectronicReceipt.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/ElectronicReceiptMethod.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/ObjectSerializer.php' );
// require_once( dirname(__FILE__) .'/Vpg/lib/Model/PaymentIntentAdditionalDataModel.php' );
// require_once(dirname(__FILE__) .'/Vpg/lib/Model/ResponseCodeConstants.php');

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Helper;
use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Sales\Model\Order;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

		$payment = $buildSubject['payment'];

        return [ 'IGNORED' => [ 'IGNORED' ] ];
    }

	/**
	 * getNumberFormat
	 */
	public function getNumberFormat( $number )
	{
		return number_format( $number ?? 0 , 2, '.', '' );
	}
}
