<?php
/**
 * Plugin Name: Give - Example Gateway
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

// Filter through the gateway data to add your own data to the $gatewayData param
add_filter(
    sprintf("givewp_create_payment_gateway_data_%s", 'onsite-example-test-gateway'),
    static function ($gatewayData) {
        // Step 1: Validate the data you need to add to the gatewayData (This comes from your gateway fields).
        if (isset($_POST['example-gateway-id'])) {
            // Step 2: Assign the sanitized data to the gatewayData as an array item.
            $gatewayData['example_payment_id'] = sanitize_text_field($_POST['example-gateway-id']);
        }

        // Step 3: Return the gatewayData array.
        return $gatewayData;
    }
);