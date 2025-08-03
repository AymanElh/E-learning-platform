<?php

namespace App\Services;

use App\Interfaces\PaymentProviderInterface;

class PaymentService
{
    private PaymentProviderInterface $paymentProvider;

    public function __construct(string $provider = 'stripe')
    {
        $this->paymentProvider = $this->createProvider($provider);
    }

    private function createProvider(string $provider): StripePaymentService
    {
        return match($provider) {
            'stripe' => new StripePaymentService(),
            // other payment services (paypal, cmi, ...)

//            default => new StripePaymentService(),
        };
    }

    public function createPayment(array $data): array
    {
        return $this->paymentProvider->createPaymentIntent($data);
    }

    public function confirmPayment(string $paymentId): array
    {
        return $this->paymentProvider->confirmPayment($paymentId);
    }

    public function refundPayment(string $paymentId, ?int $amount = null): array
    {
        return $this->paymentProvider->refundPayment($paymentId, $amount);
    }

    public function getPaymentStatus(string $paymentId): array
    {
        return $this->paymentProvider->getPaymentStatus($paymentId);
    }
}
