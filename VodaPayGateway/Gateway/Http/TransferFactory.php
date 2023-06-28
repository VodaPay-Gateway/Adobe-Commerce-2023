<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use VPG\VodaPayGateway\Gateway\Request\MockDataRequest;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/tFactoryfile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);
        $Zlogger->info("Welcome to the transfer Factory");

        return $this->transferBuilder
            ->setBody($request)
            ->setUri('http://api.vodapaygatewayuat.vodacom.co.za/V2/Pay/OnceOff')
            ->setMethod('POST')
            ->setHeaders(
                [
                    'api-key' => 'a7a46c7d-3e4e-40c8-9de5-49ec7c4bfc44',
                    'test' => 'false'
                ]
            )
            ->build();
    }
}
