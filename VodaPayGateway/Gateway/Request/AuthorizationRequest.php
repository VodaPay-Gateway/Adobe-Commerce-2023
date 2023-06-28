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
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/newfile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);
		//$Zlogger->info('text message');
		//$Zlogger->info('Array Log'.print_r($myArray, true));
        $Zlogger->info('Logger test start:');
		//$paymentObj = Helper\SubjectReader::readPayment($subject);
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
			$Zlogger->info('first one result : '. !isset($buildSubject['payment']));
			$Zlogger->info('second one result : '. !$buildSubject['payment'] instanceof PaymentDataObjectInterface);
			$Zlogger->info('Logger obj fail : '. json_encode($buildSubject['payment']));

            throw new \InvalidArgumentException('Payment data object should be provided');
        }


		$Zlogger->info('Passed if statement');

		try
		{
				$Zlogger->info('Inside try');

				$payloadVodapay = new \VodaPayGatewayClient\Model\VodaPayGatewayPayment();

				/** @var PaymentDataObjectInterface $payment */
				$payment = $buildSubject['payment'];
				$Zlogger->info('got payment obj');
				$order = $payment->getOrder();
				$address = $order->getShippingAddress();
				$Zlogger->info('got addresss');
				$Zlogger->info("Cell number: ". $address->getTelephone());
				//$Zlogger->info(json_encode($address));
				//$amount = intval($this->getTotalAmount( $order ) * 100);// The amount must be in cents.
				$amt = $buildSubject['amount'];
				//$this->logger->debug('order : '. json_encode($order));
				$amount = intval($amt * 100);// The amount must be in cents.

				$payloadVodapay->setAmount($amount);
				$Zlogger->info('amount set');
				$rlength = 10;
				$retrievalReference =   substr(
					str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
						ceil($rlength/strlen($x)) )),1,32
				);
				$Zlogger->info('ref '. $retrievalReference);
				//$Zlogger->info('order obj'. is_null($order));
				$Zlogger->info('ref 1'. $order->getOrderIncrementId());
				$retrievalReference = str_pad(ltrim($order->getOrderIncrementId(), '0'), 12, $retrievalReference, STR_PAD_LEFT);
				$Zlogger->info('ref 2 '. $retrievalReference);
				$payloadVodapay->setTraceId(strval($retrievalReference));
				$payloadVodapay->setEchoData(json_encode(['order_id'=>$order->getOrderIncrementId()]));
				$additionData = new \VodaPayGatewayClient\Model\PaymentIntentAdditionalDataModel;
				$styling = new \VodaPayGatewayClient\Model\Styling;
				$styling->setLogoUrl("");
				$styling->setBannerUrl("");
				$payloadVodapay->setStyling($styling);	
				$Zlogger->info('set styling ');
				$Zlogger->info('Object '. $payloadVodapay);
				$peripheryData = new \VodaPayGatewayClient\Model\Notifications;
				$peripheryData->setCallbackUrl('https://vodapay.magento.com/vodapaygateway/redirect/success');
				$eReceipt = new \VodaPayGatewayClient\Model\ElectronicReceipt;
				$Zlogger->info('set '. $peripheryData);
				$Zlogger->info('set erecipts '. $eReceipt);
				$eReceipt->setMethod(\VodaPayGatewayClient\Model\ElectronicReceiptMethod::SMS);
				$Zlogger->info('set erecipts '. $eReceipt);
				$eReceipt->setAddress($address->getTelephone());
				$payloadVodapay->setElectronicReceipt($eReceipt);
				$payloadVodapay->setNotifications($peripheryData);
				$Zlogger->info(strval($payloadVodapay));
				return json_decode(json_encode(strval($payloadVodapay)), true);

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
