<?php

namespace VPG\VodaPayGateway\Controller\Checkout;

require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ModelInterface.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\VodaPayGatewayPayment.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\Notifications.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\Styling.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ElectronicReceipt.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\ElectronicReceiptMethod.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\ObjectSerializer.php' );
require_once( dirname( __FILE__ ) .'\..\..\Vpg\lib\Model\PaymentIntentAdditionalDataModel.php' );
require_once(dirname(__FILE__) .'\..\..\Vpg\lib\Model\ResponseCodeConstants.php');


use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Helper;

use Magento\Sales\Model\Order;

class Index extends AbstractAction {

    private function getPayload($order) {
        if($order == null) {
            $this->getLogger()->debug('Unable to get order from last lodged order id. Possibly related to a failed database call');
            $this->_redirect('checkout/onepage/error', array('_secure'=> false));
        }

        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $billingAddressParts = preg_split('/\r\n|\r|\n/', $billingAddress->getData('street'));
        $shippingAddressParts = preg_split('/\r\n|\r|\n/', $shippingAddress->getData('street'));

        $orderId = $order->getRealOrderId();
        $data = array(
            'x_currency' => $order->getOrderCurrencyCode(),
            'x_url_callback' => $this->getDataHelper()->getCompleteUrl(),
            'x_url_complete' => $this->getDataHelper()->getCompleteUrl(),
            'x_url_cancel' => $this->getDataHelper()->getCancelledUrl($orderId),
            'x_shop_name' => $this->getDataHelper()->getStoreCode(),
            'x_account_id' => $this->getGatewayConfig()->getMerchantNumber(),
            'x_reference' => $orderId,
            'x_invoice' => $orderId,
            'x_amount' => $order->getTotalDue(),
            'x_customer_first_name' => $order->getCustomerFirstname(),
            'x_customer_last_name' => $order->getCustomerLastname(),
            'x_customer_email' => $order->getData('customer_email'),
            'x_customer_phone' => $billingAddress->getData('telephone'),
            'x_customer_billing_address1' => $billingAddressParts[0],
            'x_customer_billing_address2' => count($billingAddressParts) > 1 ? $billingAddressParts[1] : '',
            'x_customer_billing_city' => $billingAddress->getData('city'),
            'x_customer_billing_state' => $billingAddress->getData('region'),
            'x_customer_billing_zip' => $billingAddress->getData('postcode'),
            'x_customer_shipping_address1' => $shippingAddressParts[0],
            'x_customer_shipping_address2' => count($shippingAddressParts) > 1 ? $shippingAddressParts[1] : '',
            'x_customer_shipping_city' => $shippingAddress->getData('city'),
            'x_customer_shipping_state' => $shippingAddress->getData('region'),
            'x_customer_shipping_zip' => $shippingAddress->getData('postcode'),
            'x_test' => 'false'
        );

        foreach ($data as $key => $value) {
            $data[$key] = preg_replace('/\r\n|\r|\n/', ' ', $value);
        }

        $apiKey = $this->getGatewayConfig()->getApiKey();
        $signature = $this->getCryptoHelper()->generateSignature($data, $apiKey);
        $data['x_signature'] = $signature;

        return $data;
    }

    private function postToCheckout($checkoutUrl, $payload)
    {
        echo
        "<html>
            <body>
            <form id='form' action='$checkoutUrl' method='post'>";
        foreach ($payload as $key => $value) {
            echo "<input type='hidden' id='$key' name='$key' value='".htmlspecialchars($value, ENT_QUOTES)."'/>";
        }
        echo
            '</form>
            </body>';
        echo
            '<script>
                var form = document.getElementById("form");
                form.submit();
            </script>
        </html>';
    }

    /**
     * 
     *
     * @return void
     */
    public function execute() {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/indexControllerfile.log');
            $Zlogger = new \Zend_Log();
            $Zlogger->addWriter($writer);
            $order = $this->getOrder();
            $Zlogger->info("String: ". $order->getState());
            $address = $order->getShippingAddress();

            $rlength = 10;
				$retrievalReference =   substr(
					str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
						ceil($rlength/strlen($x)) )),1,32
				);
				$retrievalReference = str_pad(ltrim($order->getRealOrderId(), '0'), 12, $retrievalReference, STR_PAD_LEFT);
				$echoData = json_encode(['order_id'=>$order->getRealOrderId()]);
				$additionData = new \VodaPayGatewayClient\Model\PaymentIntentAdditionalDataModel;
				$styling = new \VodaPayGatewayClient\Model\Styling;
				$styling->setLogoUrl("");
				$styling->setBannerUrl("");
                $amount = intval($order->getTotalDue() * 100);
				$peripheryData = new \VodaPayGatewayClient\Model\Notifications;
				$peripheryData->setCallbackUrl('https://vodapay.magento.com/vpg/checkout/callback');
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
                $var =  [
					"echoData" => json_encode($echoData),
					"traceId" => $retrievalReference,
					"amount" => $amount,
					"notifications" => $peripheryData,
					"styling" => $styling,
					"electronicReceipt" => $eReceipt
				];

                $Zlogger->info(json_encode($var));
                $client = new \GuzzleHttp\Client([
                    'headers' => [
                        'api-key' => 'c5fee168-01e1-4a04-b33e-a0805d6d2735',
                        'test' => 'false',
                        'Content-Type' => 'application/json'
                    ],
                    'verify'=> false,
                    //'debug' => true,
                    'connect_timeout' => 60
                ]);
    
        $response = $client->post('http://api.vodapaygatewayuat.vodacom.co.za/V2/Pay/OnceOff',
            ['body' => strval(json_encode($var))]
        ); 
        if($response->getStatusCode() == 200){
            $Zlogger->info("Response 200");
            $responseJson = $response->getBody()->getContents();
            $responseObj = json_decode($responseJson);
    
            $responseCode = $responseObj->data->responseCode;
            $Zlogger->info('Response'. json_encode($responseObj->data));
            if(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getGoodResponseCodeList())){
                //SUCCESS
                if($responseCode == "00"){
                    //$peripheryData = $responseObj->peripheryData;
                    //$peripheryDataObj = (object) $peripheryData;
                    $initiationUrl = $responseObj->data->initiationUrl;
                    $Zlogger->info('Initiation URL: '. $initiationUrl);
                    //header("Location: $initiationUrl");
                    $this->_redirect($initiationUrl);
                    // return [
                    //     "succeeded" => true,
                    //     "initiationUrl" => $initiationUrl
                    // ];
                }
            }elseif(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getBadResponseCodeList())){
            //     //FAILURE
            //     $responseMessages = \VodaPayGatewayClient\Model\ResponseCodeConstants::getResponseText();
            //     $failureMsg = $responseMessages[$responseCode];
            //     return [
            //         "succeeded" => false,
            //         "error" => $failureMsg
            //     ];
            }
    
        }
            // if ($order->getState() === Order::STATE_PENDING_PAYMENT) {
            //     $payload = $this->getPayload($order);
            //     $this->postToCheckout($this->getGatewayConfig()->getGatewayUrl(), $payload);
            // } else if ($order->getState() === Order::STATE_CANCELED) {
            //     $errorMessage = $this->getCheckoutSession()->getOxipayErrorMessage(); //set in InitializationRequest
            //     if ($errorMessage) {
            //         $this->getMessageManager()->addWarningMessage($errorMessage);
            //         $errorMessage = $this->getCheckoutSession()->unsOxipayErrorMessage();
            //     }
            //     $this->getCheckoutHelper()->restoreQuote(); //restore cart
            //     $this->_redirect('checkout/cart');
            // } else {
            //     $this->getLogger()->debug('Order in unrecognized state: ' . $order->getState());
            //     $this->_redirect('checkout/cart');
            // }
        } catch (Exception $ex) {
            $this->getLogger()->debug('An exception was encountered in oxipay/checkout/index: ' . $ex->getMessage());
            $this->getLogger()->debug($ex->getTraceAsString());
            $this->getMessageManager()->addErrorMessage(__('Unable to start Oxipay Checkout.'));
        }
    }

}
