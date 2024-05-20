<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @inheritDoc
 */
class ExampleGatewayOnsiteClass extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'onsite-example-test-gateway';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Example Gateway - Onsite', 'example-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Example Gateway - Onsite', 'example-give');
    }

    /**
     * Display gateway fields for v2 donation forms
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // Step 1: add any gateway fields to the form using html.  In order to retrieve this data later the name of the input must be inside the key gatewayData (name='gatewayData[input_name]').
        // Step 2: you can alternatively send this data to the $gatewayData param using the filter `givewp_create_payment_gateway_data_{gatewayId}`.
        return "<div><input type='text' name='gatewayData[example-gateway-id]' placeholder='Example gateway field' /></div>";
    }

    /**
     * Register a js file to display gateway fields for v3 donation forms
     */
    public function enqueueScript(int $formId)
    {
        wp_enqueue_script('onsite-example-gateway', plugin_dir_url(__FILE__) . 'js/onsite-example-gateway.js', ['react', 'wp-element'], '1.0.0', true);
    }

    /**
     * Send form settings to the js gateway counterpart
     */
    public function formSettings(int $formId): array
    {
        return [
            'clientKey' => '1234567890'
        ];
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        try {
            // Step 1: Validate any data passed from the gateway fields in $gatewayData.  Throw the PaymentGatewayException if the data is invalid.
            if (empty($gatewayData['example-gateway-id'])) {
                throw new PaymentGatewayException(__('Example payment ID is required.', 'example-give' ) );
            }

            // Step 2: Create a payment with your gateway.
            $response = $this->exampleRequest(['transaction_id' => $gatewayData['example-gateway-id']]);

            // Step 3: Return a command to complete the donation. You can alternatively return PaymentProcessing for gateways that require a webhook or similar to confirm that the payment is complete. PaymentProcessing will trigger a Payment Processing email notification, configurable in the settings.
            return new PaymentComplete($response['transaction_id']);
        } catch (Exception $e) {
            // Step 4: If an error occurs, you can update the donation status to something appropriate like failed, and finally throw the PaymentGatewayException for the framework to catch the message.
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'example-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        // Step 1: refund the donation with your gateway.
        // Step 2: return a command to complete the refund.
        return new PaymentRefunded();
    }


    /**
     * Example request to gateway
     */
    private function exampleRequest(array $data): array
    {
        return array_merge([
            'success' => true,
            'transaction_id' => '1234567890',
            'subscription_id' => '0987654321',
        ], $data);
    }
}
