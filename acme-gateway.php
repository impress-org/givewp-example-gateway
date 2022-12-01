<?php
/**
 * Plugin Name: ACME Gateway for GiveWP
 * Description: Adds support for ACME Test donations to the GiveWP donation plugin.
 * Version: 0.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: acme-give
 * Domain Path: /languages
 */

// this registers the gateway. 
add_action('givewp_register_payment_gateway', function ($paymentGatewayRegister) {
    include 'class-acme-gateway.php';  
    $paymentGatewayRegister->registerGateway(AcmeGatewayOffsiteClass::class);
});