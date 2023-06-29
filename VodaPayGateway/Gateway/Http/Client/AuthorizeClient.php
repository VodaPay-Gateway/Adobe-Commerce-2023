<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

require_once(dirname(__FILE__) .'\..\..\..\Vpg\lib\Model\ResponseCodeConstants.php');

class AuthorizeClient implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/clientfile.log');
		$Zlogger = new \Zend_Log();
		$Zlogger->addWriter($writer);
        $Zlogger->info("Welcome to the client");
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri(),
        ];
        
        // $client = new \GuzzleHttp\Client([
        //     'headers' => $transferObject->getHeaders(),
        //     'verify'=> false,
        //     //'debug' => true,
        //     'connect_timeout' => 60
        // ]);
    
        // $response = $client->post($transferObject->getUri(),
        //     ['body' => strval(json_encode($transferObject->getBody()))]
        // ); 
        // $Zlogger->info(json_encode($response));
        // if($response->getStatusCode() == 200){
        //     $Zlogger->info("Response 200");
        //     $responseJson = $response->getBody()->getContents();
        //     $responseObj = json_decode($responseJson);
    
        //     $responseCode = $responseObj->data->responseCode;
        //     $Zlogger->info(json_encode($responseObj->data));
        //     if(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getGoodResponseCodeList())){
        //         //SUCCESS
        //         if($responseCode == "00"){
        //             //$peripheryData = $responseObj->peripheryData;
        //             //$peripheryDataObj = (object) $peripheryData;
        //             $initiationUrl = $responseObj->data->initiationUrl;
        //             $Zlogger->info('Initiation URL: '. $initiationUrl);
        //             header("Location: $initiationUrl");
        //             return [
        //                 "succeeded" => true,
        //                 "initiationUrl" => $initiationUrl
        //             ];
        //         }
        //     }elseif(in_array($responseCode, \VodaPayGatewayClient\Model\ResponseCodeConstants::getBadResponseCodeList())){
        //         //FAILURE
        //         $responseMessages = \VodaPayGatewayClient\Model\ResponseCodeConstants::getResponseText();
        //         $failureMsg = $responseMessages[$responseCode];
        //         return [
        //             "succeeded" => false,
        //             "error" => $failureMsg
        //         ];
        //     }
    
        // }
        //return $result;
        $response = [ 'IGNORED' => [ 'IGNORED' ] ];
        return $response;
    }

    /**
     * Generates response
     *
     * @return array
     */
    protected function generateResponseForCode($resultCode)
    {

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID' => $this->generateTxnId()
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }

    /**
     * @return string
     */
    protected function generateTxnId()
    {
        return md5(mt_rand(0, 1000));
    }

    /**
     * Returns result code
     *
     * @param TransferInterface $transfer
     * @return int
     */
    private function getResultCode(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int)$headers['force_result'];
        }

        return $this->results[mt_rand(0, 1)];
    }

    /**
     * Returns response fields for result code
     *
     * @param int $resultCode
     * @return array
     */
    private function getFieldsBasedOnResponseType($resultCode)
    {
        switch ($resultCode) {
            case self::FAILURE:
                return [
                    'FRAUD_MSG_LIST' => [
                        'Stolen card',
                        'Customer location differs'
                    ]
                ];
        }

        return [];
    }
}
