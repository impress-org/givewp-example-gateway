<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;


/**
 * @inheritDoc
 */
class ExampleGatewayOffsiteClass extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'handleCreatePaymentRedirect',
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
     * Register a js file to display gateway fields for v3 donation forms
     */
    public function enqueueScript(int $formId)
    {
        wp_enqueue_script('offsite-example-gateway', plugin_dir_url(__FILE__) . 'js/offsite-example-gateway.js', ['react', 'wp-element'], '1.0.0', true);
    }

    /**
     * Send form settings to the js gateway counterpart
     */
    public function formSettings(int $formId): array
    {
        return [
            'message' => __('You will be taken away to Example to complete the donation!', 'example-give'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // For an offsite gateway, this is just help text that displays on the form. 
        return "<div class='example-offsite-help-text'>
                    <p>You will be taken away to Example to complete the donation!</p>
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
        // Step 1: generate a secure gateway route URL that will be used to redirect the donor to the gateway.
        // The args you pass it will be available in the $queryParams parameter in the secureRouteMethod that you defined below.
        $returnUrl = $this->generateSecureGatewayRouteUrl(
            'handleCreatePaymentRedirect',
            $donation->id,
            [
                'givewp-donation-id' => $donation->id,
                'givewp-success-url' => urlencode(give_get_success_page_uri()),
                // this would likely be a transaction ID from the gateway upon return.
                'givewp-gateway-transaction-id' => '123456789',
            ]
        );

        // Step 2: Get the parameters you need to send to the gateway.
        $queryParams = array_merge(
            $this->getExampleParameters($donation),
            // this just an example of a return url parameter, this will be specific to your gateway.
            ['return_url' => $returnUrl]
        );

        // Step 3: Generate the URL to redirect the donor to, using the queryParams you created that contains the secure gateway route URL.
        $gatewayUrl = add_query_arg($queryParams, "https://example.com");

        // Step 4: Return a RedirectOffsite command with the generated URL to redirect the donor to the gateway.
        return new RedirectOffsite($gatewayUrl);
    }


    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @throws Exception
     */
    protected function handleCreatePaymentRedirect(array $queryParams): RedirectResponse
    {
        // Step 1: Use the $queryParams to get the data you need to complete the donation.
        $donationId = $queryParams['givewp-donation-id'];
        $gatewayTransactionId = $queryParams['givewp-gateway-transaction-id'];
        $successUrl = $queryParams['givewp-success-url'];

        // Step 2: Typically you will find the donation from the donation ID.
        /** @var Donation $donation */
        $donation = Donation::find($donationId);

        // Step 3: Use the Donation model to update the donation based on the transaction and response from the gateway.
        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = $gatewayTransactionId;
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from Example-Test Gateway Offsite.'
        ]);

        // Step 4: Return a RedirectResponse to the GiveWP success page.
        return new RedirectResponse($successUrl);
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
}