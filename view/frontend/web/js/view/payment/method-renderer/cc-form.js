define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        window.pagofacil_cc_form = '';
        let pf_options = new Object();
        const cart_total = window.checkoutConfig.payment.total;
        let installments_id = 'pagofacil_pagofacildirect_monthly_installments';
        let branch_id = window.checkoutConfig.payment.branch_id;
        let msi_url = 'http://api.core.tech/Woocommerce3ds/Configuration/msi/';
        
        var configPaymentMethod = function(){
            
            if(pf_options.enable_3ds == 1){
                pagofacil_cc_form = 'Pagofacil_Pagofacildirect/payment/pagofacil-form-3ds';
            } else {
                pagofacil_cc_form = 'Pagofacil_Pagofacildirect/payment/pagofacil-form';
            }
        }

        var getConfiguration = function ( creditCardNumber, branch_id, monto ) {

            $.ajax({
                url : msi_url,
                type: 'GET',
                data: { "creditCardNumber": creditCardNumber, "param15" : branch_id, "param16" : cart_total },
                success: function ( response ) {

                    let jsonResponse = JSON.parse(response);
                    let items = jsonResponse.configuration;
                    items = ( typeof items === "undefined" ) ? [] : items;

                    createPaymentForm( items );
                    storeConfiguration( items );
                },
                error: function (xhr, status, error) {
                    $('#'+installments_id).prop( "disabled", true );
                }
            });

        }

        var createPaymentForm = function( items ) {
            $('#'+installments_id).empty();

            let optionsDefault = `
                <option value="" label="Forma de Pago" selected="selected" disabled="disabled">Forma de Pago</option>
                <option value="00" label="Contado">Contado</option>
            `;

            let paymentAmount = cart_total;

            let options = items.map(item => {
                if( validatePaymentAmount( paymentAmount,item ) ){
                    return  `<option value="${item.monthlyPayment}">${item.monthlyPayment} Meses</option>`;
                }
            }).join('');
            
            $('#'+installments_id).append( optionsDefault );
            $('#'+installments_id).append( options );
            $('#'+installments_id).prop( "disabled", false );
        }


        var storeConfiguration = function ( items ) {
            let localStorage = window.localStorage;
            localStorage.setItem( 'configurationItems', JSON.stringify( items ) );
        }

        var validatePayment = function ( month ) {

            if( month == "00" ){
                return true;
            }

            let storeItems = JSON.parse( localStorage.getItem('configurationItems') );
            let configuration = storeItems.find( item => item.monthlyPayment == month );

            let minAmount = parseFloat( configuration.minLimit );
            let maxAmount = parseFloat( configuration.maxLimit );
            let amount = parseFloat( $("#monto_3ds").val() );

            return ( amount >= minAmount ) && ( amount <= maxAmount );
        }

        var validatePaymentAmount = function ( amount, objectConfiguration ) {

            let minPaymentAmount = parseFloat( objectConfiguration.min );
            let maxPaymentAmount = parseFloat( objectConfiguration.max );
            let paymentAmount = parseFloat( amount );

            return ( paymentAmount >= minPaymentAmount ) && ( paymentAmount <= maxPaymentAmount );
        }

        var loadPfOptions = function(){
            
            pf_options.code = 'pagofacil_pagofacildirect';
            pf_options.extra_data = '&monto='+cart_total+'&response_redirect=';
            pf_options.enable_3ds = window.checkoutConfig.payment.enable_3ds;
            pf_options.threeds_uri = window.checkoutConfig.payment.threeds_uri;
            pf_options.transaction_threeds = window.checkoutConfig.payment.transaction_3ds;
            pf_options.response_redirect = window.location.protocol + '//' 
                                           + window.location.hostname 
                                           + '/pagofacil/payment/success/?form_key='
                                           + jQuery.cookie('form_key')
                                           + '%26id='
                                           + btoa(parseInt(window.checkoutConfig.quoteData.entity_id));
        }
        
        $(document).on('blur', '#pagofacil_pagofacildirect_cc_number', function () {
            $('#'+installments_id).prop( "disabled", true );
            getConfiguration( $(this).val(), branch_id, cart_total );
        });

        $(document).ready(function(){
            loadPfOptions();
            configPaymentMethod();
        });
        
        return Component.extend({
            defaults: {
                //template: 'Pagofacil_Pagofacildirect/payment/pagofacil-form'
                template: window.pagofacil_cc_form
            },
            getCode: function() {
                return 'pagofacil_pagofacildirect';
            },

            isActive: function() {
                return true;
            },
            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonthUpdate(),
                        'cc_number': this.creditCardNumber(),
                        'monthly-installments': this.getMonthlyInstallmentSelect(),
                    }
                };
            },
            getMonthlyInstallmentSelect: function () {
                let monthly = document.querySelector('#' + this.getCode() + '_monthly_installments').value;

                /*if (1 === monthly.toString().length) {
                    monthly = '0' +monthly;
                }*/

                return monthly;
            },
            creditCardExpMonthUpdate: function () {
                let expiration = this.creditCardExpMonth();

                if (undefined === expiration) {
                    return expiration;
                }

                if (1 === expiration.toString().length) {
                    expiration = '0'+ expiration;
                }

                return expiration;
            },
            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
            threedsPlaceOrder: function(){
                
                if(window.isCustomerLoggedIn){
                    
                    let transaction = pf_options.transaction_threeds + pf_options.extra_data + pf_options.response_redirect;
                    let pf_user = this.getParameterByName('idUsuario', transaction);
                    let transaction_encoded = btoa(transaction);
                    let url_redirect = pf_options.threeds_uri + 'pf_user='+pf_user+'&data=' + transaction_encoded;
                    window.location.replace(url_redirect);
                } else {
                    window.location.replace(window.location.protocol + '//' + window.location.hostname + '/customer/account/login/');
                }
                
            }, 
            getParameterByName: function (name, url) {
                name = name.replace(/[\[\]]/g, '\\$&');
                var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }            
        });
    }
);