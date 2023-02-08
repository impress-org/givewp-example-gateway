<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

//Required. used to create the new class of the custom gateway
// Required.
// Required.
// Required.
// Required.
// Required for Recurring Donations.
// Required for Recurring Donations.
// Required for Recurring Donations.
// Optional but highly recommended to add notes especially in the event of errors or updates to donations
// Optional. Required if you want to catch and record payment gateway errors.


/**
 * Class AcmeGatewayOnsiteClass
 *
 */
class AcmeGatewayOnsiteClass extends PaymentGateway
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
        return 'onsite-acme-test-gateway';
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
        return __('Onsite ACME Test Gateway', 'acme-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Onsite ACME Test Gateway', 'acme-give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // Markup added here
        return "<div id='acme-card-field'>A Field</div>
                <div id='acme-expiration-field'>B Field</div>";
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData = null): GatewayCommand
    {   
        // some functions to process a payment (will vary based on the SDK of the gateway). 
        
        try {
            return new PaymentComplete("onsite-acme-gateway-transaction-id-$donation->id");
        
        } catch (Exception $e) {
            $donation->status = DonationStatus::FAILED();
            $errorMessage = $e->getMessage();

            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'acme-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }
    }

    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): GatewayCommand {

        try {
            return new SubscriptionComplete(
                "os-acme-gateway-transaction-id-$donation->id",
                "os-acme-gateway-subscription-id-$subscription->id"
            );
        
        } catch (Exception $e) {
            $donation->status = DonationStatus::FAILED();
            $errorMessage = $e->getMessage();

            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'acme-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }
    }

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        // some functions to process a refund (will vary based on the SDK of the gateway).
        
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();

        
    }

    
    /**
     * @since 2.23.0
     *
     * @return void
     */
    private function updateSubscription(Subscription $subscription)
    {
        $subscription->status = SubscriptionStatus::ACTIVE();
        $subscription->transactionId = "acme-test-gateway-transaction-id";
        $subscription->save();
    }

    public function updateSubscriptionPaymentMethod(Subscription $subscription, $gatewayData=null)
    {

    }
    public function updateSubscriptionAmount(Subscription $subscription, $newRenewalAmount )
    {
    }
}