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
                template: 'VPG_VodapayGateway/payment/form'
            },
            redirectAfterPlaceOrder: false,
            initObservable: function () {
                this._super();
                return this;
            },
            getCode: function() {
                // console.log("checkout", window.checkoutConfig);
                // let customerDetails = customer.customerData;
                // console.log("Quote", quote.billingAddress)
                // console.log(customerDetails);
                return 'vodapay_gateway';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'check': 'Check'
                    }
                };
            },
            redirectAfterPlaceOrder: false,
            // getTransactionResults: function() {
            //     return _.map(window.checkoutConfig.payment.vodapay_gateway.transactionResults, function(value, key) {
            //         return {
            //             'value': key,
            //             'transaction_result': value
            //         }
            //     });
            // },
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            // getInstructions: function () {
            //     return window.checkoutConfig.payment.instructions[this.item.method];
            // },
            // isAvailable: function() {
            //     return quote.totals().grand_total <= 0;
            // },
            afterPlaceOrder: function () {

                window.location.replace(url.build('vpg/checkout/index'));
                //console.log("checkout", window.checkoutConfig);
                let customerDetails = customer.customerData;
                // console.log("Quote", quote.billingAddress)

                // window.location.replace( url.build(window.checkoutConfig.payment.vodapay.redirectUrl.vodapay) );
                 //window.location.replace(url.build('http://google.com'));
               // window.location.replace( "https://uat.traderoot.com:29083/home/VPS855608206300/paymentpage?sessionId=db7e39cf-839b-4d83-978c-18143cb7bdb4" );
            },
            /** Returns payment acceptance mark link path */
            // getPaymentAcceptanceMarkHref: function() {
            //     return window.checkoutConfig.payment.vodapay.paymentAcceptanceMarkHref;
            // },
            // /** Returns payment acceptance mark image path */
            // getPaymentAcceptanceMarkSrc: function() {
            //     return window.checkoutConfig.payment.vodapay.paymentAcceptanceMarkSrc;
            // }

        });
    }
);
