<?php

namespace VPG\VodaPayGateway\Controller\Checkout;

use Magento\Sales\Model\Order;


class Callback extends AbstractAction {

    public function execute() {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/callbackControllerfile.log');
        $Zlogger = new \Zend_Log();
        $Zlogger->addWriter($writer);
        $results = $_GET;

        $responseObj = json_decode(base64_decode($results['data']));
        $Zlogger->info("Vpg Callback " . json_encode($responseObj));
        $responseCode = $responseObj->responseCode;
        $Zlogger->info($responseCode);
        $echoData = $responseObj->echoData;
        $Zlogger->info("Vpg Callback " . json_encode($echoData));
        $orderId = json_decode(json_decode($echoData, true));
        //$Zlogger->info("Echo data ". $orderId);
        $Zlogger->info("Echo data ". $orderId->order_id);
        $order = $this->getOrderById($orderId->order_id);
        if(!$order) {
            $this->getLogger()->debug("VodaPay Gateway returned an id for an order that could not be retrieved: $orderId");
            $Zlogger->info("Order not found " . json_encode($echoData));
            $this->_redirect('checkout/onepage/error', array('_secure'=> false));
            return;
        }

        // if($result == "completed" && $order->getState() === Order::STATE_PROCESSING) {
        //     $this->_redirect('checkout/onepage/success', array('_secure'=> false));
        //     return;
        // }

        // if($result == "failed" && $order->getState() === Order::STATE_CANCELED) {
        //     $this->_redirect('checkout/onepage/failure', array('_secure'=> false));
        //     return;
        // }
        if(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getGoodResponseCodeList()))
        {
            if ($responseCode == "00") {
                $orderState = Order::STATE_PROCESSING;
    
                $orderStatus = 'vodapay_gateway_approved_order_status';
    
                $order->setState($orderState)
                    ->setStatus($orderStatus)
                    ->addStatusHistoryComment("VodaPay Gateway authorisation success. Transaction #$orderId->order_id");
    
                $payment = $order->getPayment();
                $payment->setTransactionId($orderId->order_id);
                $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
                $order->save();
                $Zlogger->info("Order Saved " . json_encode($echoData));
                $this->getMessageManager()->addSuccessMessage(__("Your payment with VodaPay Gateway is complete"));
                $this->_redirect('checkout/onepage/success', array('_secure'=> false));
            } 
        }elseif (in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getBadResponseCodeList())) {
                //FAILURE
                $responseMessages = \VodaPayGatewayClient\Model\ResponseCodeConstants::getResponseText();
                $failureMsg = $responseMessages[$responseCode];
                $Zlogger->info('Error Message' . $failureMsg);
                $this->getCheckoutHelper()->cancelCurrentOrder("Order #".($order->getId())." was rejected by VodaPay Gateway.");
                $this->getCheckoutHelper()->restoreQuote(); //restore cart
                $this->getMessageManager()->addErrorMessage(__("There was an error with you VodaPay Gateway payment"));
                $this->_redirect('checkout/cart', array('_secure'=> false));
            } else {
                $this->getCheckoutHelper()->cancelCurrentOrder("Order #".($order->getId())." was rejected by VodaPay Gateway.");
                $this->getCheckoutHelper()->restoreQuote(); //restore cart
                $this->getMessageManager()->addErrorMessage(__("There was an error with you VodaPay Gateway payment"));
                $this->_redirect('checkout/cart', array('_secure'=> false));
            }

    }

    private function statusExists($orderStatus)
    {
        $statuses = $this->getObjectManager()
            ->get('Magento\Sales\Model\Order\Status')
            ->getResourceCollection()
            ->getData();
        foreach ($statuses as $status) {
            if ($orderStatus === $status["status"]) return true;
        }
        return false;
    }

    private function invoiceOrder($order, $transactionId)
    {
        if(!$order->canInvoice()){
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice.')
                );
        }
        
        $invoice = $this->getObjectManager()
            ->create('Magento\Sales\Model\Service\InvoiceService')
            ->prepareInvoice($order);
        
        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
        }
        
        /*
         * Look Magento/Sales/Model/Order/Invoice.register() for CAPTURE_OFFLINE explanation.
         * Basically, if !config/can_capture and config/is_gateway and CAPTURE_OFFLINE and 
         * Payment.IsTransactionPending => pay (Invoice.STATE = STATE_PAID...)
         */
        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        $transaction = $this->getObjectManager()->create('Magento\Framework\DB\Transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
    }

}
