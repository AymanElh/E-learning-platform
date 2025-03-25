<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaypalService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $accessToken = "";

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.sandbox') ? "https://api-m.sandbox.paypal.com" : "https://api-m.paypal.com";
        $this->accessToken = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        if($this->accessToken) {
            return $this->accessToken;
        }
        $headers = [
            'Content-Type' => 'application/x-www-from-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' .  $this->clientSecret)
        ];
//        dd($this->clientId, $this->clientSecret);
        try {
            $response = Http::withHeaders($headers)
                ->withBody('grant_type=client_credentials')
                ->post("{$this->baseUrl}/v1/oauth2/token");

            if($response->successful()) {
                $this->accessToken = $response->json('access_token');
                return $this->accessToken;
            }

            \Log::error("PayPal authentication error: ", [
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body()
            ]);
            throw new \Exception("Failed to authenticate with PayPal: " . $response->status());
        } catch (\Exception $e) {
            \Log::error("PayPal exception: " . $e->getMessage());
            throw $e;
        }
    }

    public function create($amount, $currency): array
    {
        if ((float)$amount <= 0) {
            \Log::error('PayPal invalid amount', ['amount' => $amount]);
            throw new \Exception('PayPal requires an amount greater than zero');
        }
        $id = uuid_create();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Paypal-Request-Id' => $id
        ];

        $body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $currency,
                        "value" => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => route('api.paypal.success'),
                "cancel_url" => url('/api/v1/paypal/cancel')
            ]
        ];

        try {
            \Log::info("Paypal Request: ", ['body' => $body]);

            $response = Http::withHeaders($headers)
                ->withBody(json_encode($body))
                ->post($this->baseUrl . '/v2/checkout/orders');
//            dd($response);
            \Log::info("Paypal Response: ", [
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body()
            ]);

//            dd(Session::all());

            if($response->successful()) {
                $data = $response->json();
                return array_merge($data, ['request_id' => $id]);
            }

            \Log::error('Paypal Create order Exception:', [
                'status' => $response->status(),
                'message' => $response->json()['message']
            ]);

            throw new \Exception('Failed to create PayPal order: ' .
                ($response->json()['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            \Log::error("Paypal Create order Exception: " . $e->getMessage());
            throw $e;
        }
    }

    public function complete($orderId = null)
    {
        if (!$orderId) {
            throw new \Exception('No PayPal order ID found');
        }
//        dd($orderId);
        $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture';

        $header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Basic " . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];
        $response =  Http::withHeaders($header)->send('POST', $url);
//        dd($response->json());
        \Log::info("PayPal Capture Response: ", [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        if($response->successful()) {
            return $response->json();
        }
        throw new \Exception('Failed to capture PayPal payment: ' .  ($response->json('message')));
    }

    public function getOrderDetails($orderId)
    {
        $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId;

        $response = Http::withToken($this->accessToken)
            ->get($url);

        return $response->json();
    }

}
