/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function ($,
              Component,
              placeOrderAction,
              selectPaymentMethodAction,
              customer,
              checkoutData,
              quote,      
              additionalValidators,
              url)  {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VPG_VodaPayGateway/payment/form'
            },
            redirectAfterPlaceOrder: false,
            initialize: function() {
                this._super();
                self = this;
            },
            getCode: function() {
                return 'vodapay_gateway';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                };
            },
            getLogo:function(){
                var logo = window.checkoutConfig.payment.vodapay_gateway.logo;
                console.log(logo);
                return logo;
            },
            redirectAfterPlaceOrder: false,
            afterPlaceOrder: function () {
                window.location.replace(url.build('vpg/checkout/index'));
            },
        });
    }
);
