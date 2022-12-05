<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\GenerateDonationReceiptPageUrl;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use function Give\Framework\Http\Response\response;



/**
 * Class ACME-TestGatewayOffsite
 * 
 */
class AcmeGatewayOffsiteClass extends PaymentGateway
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
        return 'acme-test-gateway-offsite';
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
        return __('ACME Test Gateway Offsite', 'acme-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('ACME Test Gateway Offsite', 'acme-give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // For an offsite gateway, this is just help text that displays on the form. 
        return "<div class='acme-offsite-help-text'>
                    You will be taken away to ACME to complete the donation!
                </div>";
    }

    /**
     * @inheritDoc
     */
    public function getAcmeParameters(Donation $donation): array
    {
        // Sample Data to send to a gateway. Not the most secure thing to include the ID and KEYS as shown here.
        return [
            'merchant_id' => '000000000000000000000',
            'merchant_key' => '111111111111111111111',
            'cancel_url' => give_get_failed_transaction_uri(),
            'notify_url' => get_site_url() . '/?give-listener=ACME',
            'name_first' => $donation->firstName,
            'name_last' => $donation->lastName,
            'email_address' => $donation->email,
            'm_payment_id' => $donation->id,
            'amount' => $donation->amount->formatToDecimal(),
            'item_name' => $donation->formTitle,
            'item_description' => sprintf(__('Donation via GiveWP, ID %s', 'acme-give'), $donation->id),
        ];
    }
    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData = null)
    {
        
        $baseParams = $this->getAcmeParameters($donation);
        $returnUrl = ['return_url' => $this->generateSecureGatewayRouteUrl('securelyReturnFromOffsiteRedirect', $donation->id, ['give-donation-id' => $donation->id])];
        $params = array_merge($baseParams, $returnUrl);

        // To see it actually redirect, uncomment this next line, and put in a valid API URL:
        $url = add_query_arg($params, "https://example.com");
        
        return new RedirectOffsite($url);
    }

    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): GatewayCommand {

        // Create a variable with an array of additional data needed to create the subscription. 
        // Sample data is included here.
        $subscriptionParams = [
            'subscription_type' => '1', 
            'frequency' => '',
            'cycles' => '',
            'recurring_amount' => $donation->amount->formatToDecimal(),
        ];


        $baseParams = array_merge(
            $this->getAcmeParameters($donation),
            $subscriptionParams);
        $returnUrl = ['return_url' => $this->generateSecureGatewayRouteUrl('securelyReturnFromOffsiteRedirect', $donation->id, ['give-donation-id' => $donation->id, 'give-subscription-id' => $subscription->id,])];
        $params = array_merge($baseParams, $returnUrl);

        $url = add_query_arg($params, "https://example.com");
        
        return new RedirectOffsite($url);
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * 
     * @param array $queryParams
     *
     * @return RedirectResponse
     */
    protected function securelyReturnFromOffsiteRedirect(array $queryParams): RedirectResponse
    {
        $donation = Donation::find($queryParams['give-donation-id']);

        $this->updateDonation($donation);

        if ( $donation->type->isSubscription() ) {
            $subscription = Subscription::find($queryParams['give-subscription-id']);
            $this->updateSubscription($subscription);
        }

        return response()->redirectTo((new GenerateDonationReceiptPageUrl())($donation->id));
    }

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();
    }

    /**
     * @param Donation $donation
     *
     * @return void
     * @throws Exception
     */
    private function updateDonation(Donation $donation)
    {
        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = "acme-test-gateway-transaction-id";
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from ACME-Test Gateway Offsite.'
        ]);
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
}