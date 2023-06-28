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
        $this->logger->debug('Logger test : '. json_decode($buildSubject));
        // if (!isset($buildSubject['payment'])
        //     || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        // ) {
        //     throw new \InvalidArgumentException('Payment data object should be provided');
        // }



        ///** @var PaymentDataObjectInterface $payment */
        // $payment = $buildSubject['payment'];
        // $order = $payment->getOrder();
        // $address = $order->getShippingAddress();

        // $amount = intval($this->getTotalAmount( $order ) * 100);// The amount must be in cents.
		

		// $payloadVodapay = new \VodaPayGatewayClient\Model\VodaPayGatewayPayment();

		// //$this->logger->debug('order : '. json_encode($order));
		
		// $amount = intval($this->getTotalAmount( $order ) * 100);// The amount must be in cents.
		// $payloadVodapay->setAmount($amount);


		// $rlength = 10;
		// $retrievalReference =   substr(
		// 	str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		// 		ceil($rlength/strlen($x)) )),1,32
		// );
		// $retrievalReference = str_pad($order->get_order_number() ?? " ", 12, $retrievalReference, STR_PAD_LEFT);
		// $payloadVodapay->setTraceId(strval($retrievalReference));


		// $payloadVodapay->setEchoData(json_encode(['order_id'=>$order->getRealOrderId()]));


		// $additionData = new \VodaPayGatewayClient\Model\PaymentIntentAdditionalDataModel;

		// $styling = new \VodaPayGatewayClient\Model\Styling;
		// $styling->setLogoUrl("");
		// $styling->setBannerUrl("");
		// $payloadVodapay->setStyling($styling);	

		// $peripheryData = new \VodaPayGatewayClient\Model\Notifications;
		// $peripheryData->setCallbackUrl('https://vodapay.magento.com/vodapaygateway/redirect/success');
		// //$peripheryData->setNotificationUrl();
		// $payloadVodapay->setNotifications($peripheryData);

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
