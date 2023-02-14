<?php
/**
 * Plugin Name: Example Gateway for GiveWP
 * Description: Adds support for Example Test donations to the GiveWP donation plugin. Includes an on-site and off-site option showcasing how to create Gateway Add-ons for each.
 * Version: 0.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Ben Meredith
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

