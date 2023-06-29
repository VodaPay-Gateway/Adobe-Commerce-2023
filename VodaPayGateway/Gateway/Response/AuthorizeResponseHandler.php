<?php

namespace VPG\VodaPayGateway\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class AuthorizeResponseHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/responsefile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $Zlogger->info('Response arr '. json_encode($response));
        $payment = $paymentDO->getPayment();
        /** @var $payment Payment */
        $payment->setIsTransactionPending(true);
    }
}
