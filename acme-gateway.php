<?php
/**
 * Plugin Name: GiveWP Example Gateway
 * Description: Includes an on-site and off-site example gateway that showcases how to create GiveWP gateway add-ons.
 * Version: 0.1.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: GiveWP - Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: example-give
 * Domain Path: /languages
 */

// Register the gateways 
add_action('givewp_register_payment_gateway', function ($paymentGatewayRegister) {
    include 'class-offsite-example-gateway.php';
    include 'class-onsite-example-gateway.php';
    $paymentGatewayRegister->registerGateway(ExampleGatewayOffsiteClass::class);
    $paymentGatewayRegister->registerGateway(ExampleGatewayOnsiteClass::class);
});

