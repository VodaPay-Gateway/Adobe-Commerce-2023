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
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ElectronicReceipt.php' );
require( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ElectronicReceiptMethod.php' );
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
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/requestfile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

		try
		{
				$payloadVodapay = new \VodaPayGatewayClient\Model\VodaPayGatewayPayment();

				/** @var PaymentDataObjectInterface $payment */
				$payment = $buildSubject['payment'];
				$order = $payment->getOrder();
				$address = $order->getShippingAddress();
				$amt = $buildSubject['amount'];
				$amount = intval($amt * 100);// The amount must be in cents.

				$payloadVodapay->setAmount($amount);
				$rlength = 10;
				$retrievalReference =   substr(
					str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
						ceil($rlength/strlen($x)) )),1,32
				);
				$retrievalReference = str_pad(ltrim($order->getOrderIncrementId(), '0'), 12, $retrievalReference, STR_PAD_LEFT);
				$payloadVodapay->setTraceId(strval($retrievalReference));
				$payloadVodapay->setEchoData(json_encode(['order_id'=>$order->getOrderIncrementId()]));
				$additionData = new \VodaPayGatewayClient\Model\PaymentIntentAdditionalDataModel;
				$styling = new \VodaPayGatewayClient\Model\Styling;
				$styling->setLogoUrl("");
				$styling->setBannerUrl("");
				$payloadVodapay->setStyling($styling);	
				$peripheryData = new \VodaPayGatewayClient\Model\Notifications;
				$peripheryData->setCallbackUrl('https://vodapay.magento.com/vodapaygateway/redirect/success');
				$eReceipt = new \VodaPayGatewayClient\Model\ElectronicReceipt;
				$eReceipt->setMethod(\VodaPayGatewayClient\Model\ElectronicReceiptMethod::SMS);
				$number = $address->getTelephone();
				if(str_starts_with($number, '0'))
				{
					$ptn = "/^0/";
					$number =  preg_replace($ptn, "27", $number);
					$Zlogger->info("Number: ". $number);
				}
				//str_starts_with('http://www.google.com', 'http')
				$eReceipt->setAddress($number);
				$payloadVodapay->setElectronicReceipt($eReceipt);
				$payloadVodapay->setNotifications($peripheryData);
				$Zlogger->info(strval($payloadVodapay));
				return [
					"echoData" => json_encode(['order_id'=>$order->getOrderIncrementId()]),
					"traceId" => $retrievalReference,
					"amount" => $amount,
					"notifications" => $peripheryData,
					"styling" => $styling,
					"electronicReceipt" => $eReceipt
				];

		}
		catch (Exception $e) {
		
			$Zlogger->info('in catch');
			$Zlogger->info(json_encode($e));
			$newWriter = new \Zend_Log_Writer_Stream(BP . '/var/log/newExceptionfile.log');
			$newZlogger = new \Zend_Log();
			$newZlogger->addWriter($newWriter);
			$newZlogger->info('Logger Exception : '. json_encode(array('msg'=>$e->getMessage())));
		} 
    }

    /**
	 * getTotalAmount
	 */
	public function getTotalAmount( $order )
	{
		//$this->logger->debug('Grand Total : '. $order->getGrandTotal());
		//$this->logger->debug('Base Grand Total : '. $order->getBaseGrandTotal());
			$newWriter = new \Zend_Log_Writer_Stream(BP . '/var/log/getAmount.log');
			$newZlogger = new \Zend_Log();
			$newZlogger->addWriter($newWriter);
			$newZlogger->info('Logger Order Amount : '. json_encode($order));

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
