<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Subscriptions\Models\Subscription;

/**
 * Class ExampleGatewayOnsiteClass
 *
 */
class ExampleGatewayOnsiteClass extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'securelyReturnFromOffsiteRedirect'
    ];

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
        return __('Onsite Example Test Gateway', 'example-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Onsite Example Test Gateway', 'example-give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // Markup added here
        return "<div id='example-card-field'>A Field</div>
                <div id='example-expiration-field'>B Field</div>";
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData = null): GatewayCommand
    {
        try {
            // Here is where you would add logic to process a payment (will vary based on the SDK of the gateway).
            $paymentResponseExample = [
                'transaction_id' => "onsite-example-gateway-transaction-id-$donation->id"
            ];

            return new PaymentComplete($paymentResponseExample['transaction_id']);
        } catch (Exception $e) {
            $donation->status = DonationStatus::FAILED();
            $errorMessage = $e->getMessage();

            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'example-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): GatewayCommand {
        try {
            // this is where you would add logic to process a subscription (will vary based on the SDK of the gateway).
            $processSubscriptionResponseExample = [
                'transaction_id' => "os-example-gateway-transaction-id-$donation->id",
                'subscription_id' => "os-example-gateway-subscription-id-$subscription->id"
            ];

            return new SubscriptionComplete(
                $processSubscriptionResponseExample['transaction_id'],
                $processSubscriptionResponseExample['subscription_id']
            );
        } catch (Exception $e) {
            $donation->status = DonationStatus::FAILED();
            $errorMessage = $e->getMessage();

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
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        // this is where you would add logic to process a refund (will vary based on the SDK of the gateway).
        $processRefundResponseExample = [
            'success' => true,
        ];

        if ($processRefundResponseExample['success'] === true) {
            $donation->status = DonationStatus::REFUNDED();
            $donation->save();
        }
    }
}