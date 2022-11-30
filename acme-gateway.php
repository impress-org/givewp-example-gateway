<?php
/**
 * Plugin Name: Give - ACME Gateway
 * Description: Adds support for ACME Test donations to the GiveWP donation plugin.
 * Version: 0.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Ben Meredith
 * Author URI: https://givewp.com
 * Text Domain: give-acme
 * Domain Path: /languages
 */

add_action ('init', function() {
    include 'class-acme-gateway.php';    
});

add_action('before_give_init', function () {
   
        give()->registerServiceProvider(AcmeGateway\PaymentGateway\ServiceProvider::class);
});