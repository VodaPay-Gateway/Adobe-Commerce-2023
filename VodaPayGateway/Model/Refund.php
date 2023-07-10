<?php

namespace VPG\VodaPayGateway\Model;

//require_once(dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/VodaPayGatewayRefund.php');
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/ModelInterface.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/VodaPayGatewayPayment.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/Notifications.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/Styling.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/ElectronicReceipt.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/ElectronicReceiptMethod.php' );
//require_once( dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/ObjectSerializer.php' );
require_once(dirname(__FILE__) .'../Controller/Checkout/Vpg/lib/Model/ResponseCodeConstants.php');

use VPG\VodaPayGateway\Helper\Crypto;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;

class Refund extends \Magento\Payment\Model\Method\AbstractMethod implements HandlerInterface
{
	public $_isGateway = true;
	public $_canRefund = true;
	public $_canRefundInvoicePartial = true;
	public $_canCapture = true;
	public $_canCapturePartial = true;
	public $_scopeConfig;

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
		\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
		\Magento\Payment\Helper\Data $paymentData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Payment\Model\Method\Logger $logger
	) {
		parent::__construct(
			$context,
			$registry,
			$extensionFactory,
			$customAttributeFactory,
			$paymentData,
			$scopeConfig,
			$logger
		);
		$this->_scopeConfig = $scopeConfig;
	}

	public function handle(array $handlingSubject, array $response)
	{
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/refundControllerfile.log');
        $Zlogger = new \Zend_Log();
        $Zlogger->addWriter($writer);

		
		$refund_url = $response['REFUND_URL'];
		$apiKey = $response['API_KEY'];
		$env = $response['ENV'];
        $Zlogger->info('Refund '. $refund_url);
		$refund_amount = $handlingSubject['amount'];
		$payment = $handlingSubject['payment']->getPayment();
		$order = $payment->getOrder();
		$address = $order->getShippingAddress();

		//$eReceipt = new \VodaPayGatewayClient\Model\ElectronicReceipt;
		//$eReceipt->setMethod(\VodaPayGatewayClient\Model\ElectronicReceiptMethod::SMS);
		$number = $address->getTelephone();
		if(str_starts_with($number, '0'))
		{
			$ptn = "/^0/";
			$number =  preg_replace($ptn, "27", $number);
		//	$Zlogger->info("Number: ". $number);
		}
		$eReceipt = [
			'method' => 0,
			'address' => $number
		];
		if (empty($payment) || empty($payment->getData('creditmemo'))) {
            $Zlogger->info('We can\'t issue a refund transaction because there is no capture transaction.');
			throw new LocalizedException(
				__('We can\'t issue a refund transaction because there is no capture transaction.')
			);
		}


		
		$transaction_id = $payment->getData()['creditmemo']->getData('invoice')->getData('transaction_id');        
		$rlength = 10;
				$retrievalReference =   substr(
					str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
						ceil($rlength/strlen($x)) )),1,32
				);
		$retrievalReference = str_pad(ltrim($order->getRealOrderId(), '0'), 12, $retrievalReference, STR_PAD_LEFT);
		$echoData = json_encode(['transaction_id'=> $transaction_id]);
		$refund_amount = intval($handlingSubject['amount'] * 100);
		
			// protected static array $openAPITypes = [
		// 	'echo_data' => 'string',
		// 	'trace_id' => 'string',
		// 	'original_transaction_id' => 'string',
		// 	'amount' => 'int',
		// 	'payment_token' => 'string',
		// 	'notifications' => '\VodaPayGatewayClient\Model\Notifications',
		// 	'electronic_receipt' => '\VodaPayGatewayClient\Model\ElectronicReceipt',
		// ];

		$refund_details = [ 
			"echoData" => $echoData,
			"traceId" => $retrievalReference,
			"originalTransactionId" => $transaction_id,
			"amount" => $refund_amount,
			"electronicReceipt" => $eReceipt
		];

		$json = json_encode($refund_details);

		$client = new \GuzzleHttp\Client([
			'headers' => [
				'api-key' => $apiKey,
				'test' => $env == '1' ? 'true' : 'false',
				'Content-Type' => 'application/json'
			],
			'verify'=> false,
			//'debug' => true,
			'connect_timeout' => 60
		]);

		$response = $client->post($refund_url,
			['body' => strval($refund_details)]
		); 


		// Do refunding POST request using curl
		// $curl = curl_init($refund_url);
		// curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		// curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($curl, CURLOPT_HEADER, 1);
		// curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		// $response = curl_exec($curl);

		// // split and parse header and body
		// $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// $header_string = substr($response, 0, $header_size);
		// $body = substr($response, $header_size);
		// $header_rows = explode(PHP_EOL, $header_string);
		// $header_rows_trimmed = array_map('trim', $header_rows);
		// $parsed_header = ($this->parseHeaders($header_rows_trimmed));

		// curl_close($curl);

		if($response->getStatusCode() == 200){
            // $Zlogger->info("Response 200");
            $responseJson = $response->getBody()->getContents();
            $responseObj = json_decode($responseJson);
    
            $responseCode = $responseObj->data->responseCode;
            // $Zlogger->info('Response'. json_encode($responseObj->data));
            if(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getGoodResponseCodeList())){
                //SUCCESS
                if($responseCode == "00"){
                    return $this;
                }
            }elseif(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getBadResponseCodeList())){
                //FAILURE
                $error_message =  $responseObj->data->responseMessage;
				$this->_logger->error(__($error_message));
				throw new LocalizedException(__($error_message));
            }
        }
		
	}

	function parseHeaders($headers)
	{
		$head = array();
		foreach ($headers as $k => $v) {
			$t = explode(':', $v, 2);
			if (isset($t[1]))
				$head[trim($t[0])] = trim($t[1]);
			else {
				$head[] = $v;
				if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out))
					$head['response_code'] = intval($out[1]);
			}
		}
		return $head;
	}
}