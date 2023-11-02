<?php

/**
 * Example gateway api
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

    public static function refundPayment(): bool
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