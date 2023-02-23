<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
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
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // Step 1: add any gateway fields to the form using html.
        // Step 2: you can send this data to the $gatewayData param using the filter `givewp_create_payment_gateway_data_{gatewayId}`.
        return "<div><input type='text' name='example-gateway-id' placeholder='Example gateway field' /></div>";
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        try {
            // Step 1: Validate any data passed from the gateway fields in $gatewayData.  Throw the PaymentGatewayException if the data is invalid.
            if (empty($gatewayData['example_payment_id'])) {
                throw new PaymentGatewayException('Example payment ID is required.');
            }

            // Step 2: Create a payment with your gateway.
            $response = $this->exampleRequest(['transaction_id' => $gatewayData['example_payment_id']]);

            // Step 3: Return a command to complete the donation.
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
     * TODO: return command
     *
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        // this is where you would add logic to process a refund (will vary based on the SDK of the gateway).
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();
    }


    /**
     * Example request to gateway
     */
    public function exampleRequest(array $data): array
    {
        return array_merge([
            'success' => true,
            'transaction_id' => '1234567890',
            'subscription_id' => '0987654321',
        ], $data);
    }
}