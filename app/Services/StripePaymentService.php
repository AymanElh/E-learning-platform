<?php

namespace App\Services;

use App\Interfaces\PaymentProviderInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripePaymentService implements PaymentProviderInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(array $data): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $data['amount'] * 100,  // convert to cents
                'currency' => $data['currency'] ?? 'USD',
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
                'metadata' => [
                    'course_id' => $data['course_id'],
                    'user_id' => $data['user_id'],
                    'order_id' => $data['order_id'],
                ],
                'description' => $data["description"] ?? "Course Purchase",
            ]);

//            dd($paymentIntent->status);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
            ];
        } catch (ApiErrorException $e) {
            \Log::error("Stripe payment intent creation failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);


            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'payment_id' => $paymentIntent->id,
                'metadata' => $paymentIntent->metadata->toArray(),
            ];

        } catch (ApiErrorException $e) {
            \Log::error('Stripe Payment Confirmation Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(string $paymentId, ?int $amount = null): array
    {
        // TODO: Implement refundPayment() method.
    }

    public function getPaymentStatus(string $paymentId): array
    {
        // TODO: Implement getPaymentStatus() method.
    }
}
