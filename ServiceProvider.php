<?php

namespace AcmeGateway\PaymentGateway;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;


/**
 * @since 0.1
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{
    /**
     * @since 0.1
     */
    public function register()
    {
    }

    /**
     * @since 1.3.0 Use new filter hook to provide gateway data to process payment.
     *             New filter hook: https://github.com/impress-org/givewp/pull/6475
     * @since 1.2.0
     */
    public function boot()
    {
        add_action('givewp_register_payment_gateway', function (PaymentGatewayRegister $paymentGatewayRegister) {
            $paymentGatewayRegister->registerGateway(AcmeGatewayClass::class);
        });


    }
}