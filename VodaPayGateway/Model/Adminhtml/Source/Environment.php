<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VPG\VodaPayGateway\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction
 */
class Environment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('UAT'),
            ],
            [
                'value' => '1',
                'label' => __('SANDBOX'),
            ],
            [
                'value' => '2',
                'label' => __('PRODUCTION'),
            ]
        ];
    }
}
