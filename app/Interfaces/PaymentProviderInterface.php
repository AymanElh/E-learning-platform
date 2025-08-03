<?php

namespace App\Interfaces;

interface PaymentProviderInterface
{
    public function createPaymentIntent(array $data): array;
    public function confirmPayment(string $paymentIntentId): array;
    public function refundPayment(string $paymentId, ?int $amount = null): array;
    public function getPaymentStatus(string $paymentId): array;
}
