<?php
/**
 * Plugin Name: ACME Gateway for GiveWP
 * Description: Adds support for ACME Test donations to the GiveWP donation plugin. Includes an on-site and off-site option showcasing how to create Gateway Add-ons for each.
 * Version: 0.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: acme-give
 * Domain Path: /languages
 */

// Register the gateways 
add_action('givewp_register_payment_gateway', function ($paymentGatewayRegister) {
    include 'class-offsite-acme-gateway.php';  
    include 'class-onsite-acme-gateway.php';  
    $paymentGatewayRegister->registerGateway(AcmeGatewayOffsiteClass::class);
    $paymentGatewayRegister->registerGateway(AcmeGatewayOnsiteClass::class);
});

