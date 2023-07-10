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
     * @var Logger
     */
    private $logger;

    /**
     * @param ConfigInterface $config
     * @param Logger $config
     */
    public function __construct(
        ConfigInterface $config,
		Logger $logger
    ) {
        $this->config = $config;
		$this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
		//$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/requestfile.log');
		//$Zlogger = new \Zend_Log();
		//$Zlogger->addWriter($writer);
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

		$payment = $buildSubject['payment'];
        //$stateObject = $buildSubject['stateObject'];

		// $order = $payment->getOrder();

		// $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        // $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        // $stateObject->setIsNotified(false);

        return [ 'IGNORED' => [ 'IGNORED' ] ];
    }

    /**
	 * getTotalAmount
	 */
	public function getTotalAmount( $order )
	{
		//$this->logger->debug('Grand Total : '. $order->getGrandTotal());
		//$this->logger->debug('Base Grand Total : '. $order->getBaseGrandTotal());
			// $newWriter = new \Zend_Log_Writer_Stream(BP . '/var/log/getAmount.log');
			// $newZlogger = new \Zend_Log();
			// $newZlogger->addWriter($newWriter);
			// $newZlogger->info('Logger Order Amount : '. json_encode($order));

			try
			{
				$price = $this->getNumberFormat( $order->getBaseGrandTotal() );
			}
			catch(Exception $e) {
				$newZlogger->info('getTotalAmount '. $e->getMessage());
			}

		return $price;
	}

	/**
	 * getNumberFormat
	 */
	public function getNumberFormat( $number )
	{
		return number_format( $number ?? 0 , 2, '.', '' );
	}
}
