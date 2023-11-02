<?php

/**
 * This is a fake gateway api, used simply as an example throughout our example gateways.
 */
class ExampleGatewayApi
{
    public static function createPayment(array $data): array
    {
        return array_merge([
            'id' => uniqid('egpay_', true),
            'transaction_id' => '1234567890',
        ], $data);
    }

    public static function refundPayment(string $paymentId): bool
    {
        return true;
    }

    public static function createSubscription(array $data): array
    {
         return array_merge([
            'id' => uniqid('egsub_', true),
            'transaction_id' => '1234567890',
        ], $data);
    }

    public static function cancelSubscription(string $subscriptionId): bool
    {
        return true;
    }
}