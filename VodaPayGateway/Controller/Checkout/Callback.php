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
        $echoData = $responseObj->echoData;
        $Zlogger->info("Vpg Callback " . json_encode($echoData));
        $orderId = json_decode($echoData, TRUE);

        $order = $this->getOrderById($orderId);
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

        if ($result == "completed") {
            $orderState = Order::STATE_PROCESSING;
            $Zlogger->info("Order found Callback " . json_encode($echoData));

            // $orderStatus = $this->getGatewayConfig()->getOxipayApprovedOrderStatus();
            // if (!$this->statusExists($orderStatus)) {
            //     $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
            // }

            $orderStatus = 'vodapay_gateway_approved_order_status';

            $order->setState($orderState)
                ->setStatus($orderStatus)
                ->addStatusHistoryComment("VodaPay Gateway authorisation success. Transaction #$orderId");

	        $payment = $order->getPayment();
	        $payment->setTransactionId($orderId);
	        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
            $order->save();
            $Zlogger->info("Order Saved " . json_encode($echoData));
            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            // $emailSender->send($order);

            // $invoiceAutomatically = $this->getGatewayConfig()->isAutomaticInvoice();
            // if ($invoiceAutomatically) {
            //     $this->invoiceOrder($order, $transactionId);
            // }
            
            $this->getMessageManager()->addSuccessMessage(__("Your payment with VodaPay Gateway is complete"));
            $this->_redirect('checkout/onepage/success', array('_secure'=> false));
        } 
        // else {
        //     $this->getCheckoutHelper()->cancelCurrentOrder("Order #".($order->getId())." was rejected by oxipay. Transaction #$transactionId.");
        //     $this->getCheckoutHelper()->restoreQuote(); //restore cart
        //     $this->getMessageManager()->addErrorMessage(__("There was an error in the Oxipay payment"));
        //     $this->_redirect('checkout/cart', array('_secure'=> false));
        // }
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
