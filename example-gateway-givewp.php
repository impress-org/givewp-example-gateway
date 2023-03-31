<?php
/**
 * Plugin Name: Example Gateway for GiveWP
 * Description: Create your own GiveWP payment gateway using this plugin as a guide.
 * Version: 0.1.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: GiveWP - Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: example-give
 * Domain Path: /languages
 */

// Register the gateways 
add_action('givewp_register_payment_gateway', static function ($paymentGatewayRegister) {
    include 'class-offsite-example-gateway.php';
    include 'class-onsite-example-gateway.php';
    $paymentGatewayRegister->registerGateway(ExampleGatewayOffsiteClass::class);
    $paymentGatewayRegister->registerGateway(ExampleGatewayOnsiteClass::class);
});