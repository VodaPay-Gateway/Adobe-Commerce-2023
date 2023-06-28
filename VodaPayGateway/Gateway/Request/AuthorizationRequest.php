<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace VPG\VodaPayGateway\Gateway\Request;

require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ModelInterface.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\VodaPayGatewayPayment.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\Notifications.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\Styling.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\ObjectSerializer.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\PaymentIntentAdditionalDataModel.php' );

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Helper;

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
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/newfile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);
		//$Zlogger->info('text message');
		//$Zlogger->info('Array Log'.print_r($myArray, true));

        $Zlogger->info('Logger test : '. json_encode($buildSubject));
		$paymentObj = Helper\SubjectReader::readPayment($subject);
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
			$Zlogger->info('Logger obj fail : '. json_encode($buildSubject['payment']));
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

		$payloadVodapay = new \VodaPayGatewayClient\Model\VodaPayGatewayPayment();

		$Zlogger->info('Logger obj fail : '. json_encode($buildSubject['payment']));
		try
		{
				/** @var PaymentDataObjectInterface $payment */
				$payment = $buildSubject['payment'];
				$order = $payment->getOrder();
				$address = $order->getShippingAddress();
		
				$amount = intval($this->getTotalAmount( $order ) * 100);// The amount must be in cents.
				//$this->logger->debug('order : '. json_encode($order));
				$amount = intval($this->getTotalAmount( $order ) * 100);// The amount must be in cents.
				$payloadVodapay->setAmount($amount);
				$rlength = 10;
				$retrievalReference =   substr(
					str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
						ceil($rlength/strlen($x)) )),1,32
				);
				$retrievalReference = str_pad($order->get_order_number() ?? " ", 12, $retrievalReference, STR_PAD_LEFT);
				$payloadVodapay->setTraceId(strval($retrievalReference));
				$payloadVodapay->setEchoData(json_encode(['order_id'=>$order->getRealOrderId()]));
				$additionData = new \VodaPayGatewayClient\Model\PaymentIntentAdditionalDataModel;
				$styling = new \VodaPayGatewayClient\Model\Styling;
				$styling->setLogoUrl("");
				$styling->setBannerUrl("");
				$payloadVodapay->setStyling($styling);	

				$peripheryData = new \VodaPayGatewayClient\Model\Notifications;
				$peripheryData->setCallbackUrl('https://vodapay.magento.com/vodapaygateway/redirect/success');
				//$peripheryData->setNotificationUrl();
				$payloadVodapay->setNotifications($peripheryData);

		}
		catch (Exception $e) {
		
			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/newExceptionfile.log');
			$Zlogger = new \Zend_Log();
			$Zlogger->addWriter($writer);
			$Zlogger->info('Logger Exception : '. json_encode(array('msg'=>$e->getMessage())));
		} 

        return strval($payloadVodapay);
    }

    /**
	 * getTotalAmount
	 */
	public function getTotalAmount( $order )
	{
		//$this->logger->debug('Grand Total : '. $order->getGrandTotal());
		//$this->logger->debug('Base Grand Total : '. $order->getBaseGrandTotal());

        $price = $this->getNumberFormat( $order->getBaseGrandTotal() );

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
