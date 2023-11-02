<?php
/**
 * Plugin Name: Example Gateway for GiveWP
 * Description: Create your own GiveWP payment gateway using this plugin as a guide.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Author: GiveWP - Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: example-give
 * Domain Path: /languages
 */

// Register the gateways 
add_action('givewp_register_payment_gateway', static function ($paymentGatewayRegister) {
    include 'class-example-gateway-api.php';
    include 'class-offsite-example-gateway.php';
    include 'class-onsite-example-gateway.php';
    $paymentGatewayRegister->registerGateway(ExampleGatewayOffsiteClass::class);
    $paymentGatewayRegister->registerGateway(ExampleGatewayOnsiteClass::class);
});

// Register the gateways subscription module for onsite example test gateway
 add_filter("givewp_gateway_onsite-example-test-gateway_subscription_module", static function () {
        include 'class-onsite-example-gateway-subscription-module.php';

        return ExampleGatewayOnsiteSubscriptionModuleClass::class;
    }
);