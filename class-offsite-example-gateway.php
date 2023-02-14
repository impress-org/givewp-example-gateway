<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;


/**
 * Class Example-TestGatewayOffsite
 *
 */
class ExampleGatewayOffsiteClass extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'securelyReturnFromOffsiteRedirectForDonation',
        'securelyReturnFromOffsiteRedirectForSubscription',
    ];

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'example-test-gateway-offsite';
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
        return __('Example Gateway - Offsite', 'example-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Example Gateway - Offsite', 'example-give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // For an offsite gateway, this is just help text that displays on the form. 
        return "<div class='example-offsite-help-text'>
                    You will be taken away to Example to complete the donation!
                </div>";
    }

    /**
     * Get Sample Data to send to a gateway.
     */
    public function getExampleParameters(Donation $donation): array
    {
        // this is just an example but,
        // you would need to get your own merchant ID and key from the gateway.
        // Typically, these are retrieved from the GiveWP gateway settings when the admin
        // sets up the gateway using their account.
        $secureMerchantId = '000000000000000000000';
        $secureMerchantKey = '111111111111111111111';

        return [
            'merchant_id' => $secureMerchantId,
            'merchant_key' => $secureMerchantKey,
            'cancel_url' => give_get_failed_transaction_uri(),
            'notify_url' => get_site_url() . '/?give-listener=Example',
            'name_first' => $donation->firstName,
            'name_last' => $donation->lastName,
            'email_address' => $donation->email,
            'm_payment_id' => $donation->id,
            'amount' => $donation->amount->formatToDecimal(),
            'item_name' => $donation->formTitle,
            'item_description' => sprintf(__('Donation via GiveWP, ID %s', 'example-give'), $donation->id),
        ];
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData)
    {
        $baseParams = $this->getExampleParameters($donation);
        $returnUrl = [
            'return_url' => $this->generateSecureGatewayRouteUrl(
                'securelyReturnFromOffsiteRedirectForDonation',
                $donation->id,
                ['givewp-donation-id' => $donation->id]
            )
        ];
        $params = array_merge($baseParams, $returnUrl);

        // This will redirect to example.com and one of the query strings will be the URL that you can visit to simulate a successful donation.
        $url = add_query_arg($params, "https://example.com");

        return new RedirectOffsite($url);
    }

    /**
     * @inheritDoc
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): GatewayCommand {
        // Create a variable with an array of additional data needed to create the subscription.
        // Sample data is included here.
        $subscriptionParams = [
            'subscription_type' => '1',
            'frequency' => $subscription->frequency,
            'cycles' => $subscription->installments,
            'recurring_amount' => $donation->amount->formatToDecimal(),
        ];

        $baseParams = array_merge(
            $this->getExampleParameters($donation),
            $subscriptionParams
        );

        $returnUrl = [
            'return_url' => $this->generateSecureGatewayRouteUrl(
                'securelyReturnFromOffsiteRedirectForSubscription',
                $donation->id,
                [
                    'givewp-donation-id' => $donation->id,
                    'givewp-subscription-id' => $subscription->id
                ]
            )
        ];

        $params = array_merge($baseParams, $returnUrl);

        $url = add_query_arg($params, "https://example.com");

        return new RedirectOffsite($url);
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @param  array  $queryParams
     *
     * @return RedirectResponse
     * @throws Exception
     */
    protected function securelyReturnFromOffsiteRedirectForDonation(array $queryParams): RedirectResponse
    {
        /** @var Donation $donation */
        $donation = Donation::find($queryParams['givewp-donation-id']);

        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = "example-test-gateway-transaction-id";
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from Example-Test Gateway Offsite.'
        ]);

        return new RedirectResponse(give_get_success_page_uri());
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @param  array  $queryParams
     *
     * @return RedirectResponse
     * @throws Exception
     */
    protected function securelyReturnFromOffsiteRedirectForSubscription(array $queryParams): RedirectResponse
    {
        /** @var Donation $donation */
        $donation = Donation::find($queryParams['givewp-donation-id']);

        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = "example-test-gateway-transaction-id";
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from Example-Test Gateway Offsite.'
        ]);


        /** @var Subscription $subscription */
        $subscription = Subscription::find($queryParams['givewp-subscription-id']);
        $subscription->status = SubscriptionStatus::ACTIVE();
        $subscription->transactionId = "example-test-gateway-transaction-id";
        $subscription->save();


        return new RedirectResponse(give_get_success_page_uri());
    }

    /**
     *
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();
    }

    /**
     * @inerhitDoc
     * @throws Exception
     */
    public function updateSubscriptionAmount(Subscription $subscription, $newRenewalAmount)
    {
        // some functions to send the call to the gateway to update the amount. $newRenewalAmount is the updated amount of the subscriptions.
        $apiResponse = [
            'success' => true
        ];

        if (!$apiResponse['success']) {
            PaymentGatewayLog::error(
                sprintf(
                    __(
                        'Failed to update amount for subscription %s.',
                        'example-give'
                    ),
                    $subscription->id
                ),
                [
                    'Payment Gateway' => $this->getName(),
                    'Subscription' => $subscription->id,
                    'Error Code' => "999",
                    'Error Message' => "something actionable from the gateway!",
                ]
            );
            throw new PaymentGatewayException(
                __(
                    'The amount was not updated.',
                    'example-give'
                )
            );
        }

        PaymentGatewayLog::info('Amount updated ', [
            'Payment Gateway' => "noodles",
            'Subscription' => "mo problems",
        ]);
    }
}