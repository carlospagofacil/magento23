define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'pagofacil_pagofacildirect',
                component: 'Pagofacil_Pagofacildirect/js/view/payment/method-renderer/cc-form'
            }
        );

        return Component.extend({});
    }
);