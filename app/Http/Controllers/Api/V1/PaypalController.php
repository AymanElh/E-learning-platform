<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PaypalService;
use Illuminate\Http\Request;

class PaypalController extends Controller
{
    protected PaypalService $paypalService;

    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    public function success(Request $request)
    {
        \Log::info('PayPal Success: ', $request->all());
//        dd($request->all());
        try {
            $token = $request->get('token');
            $result = $this->paypalService->complete($token);
            $orderDetails = $this->paypalService->getOrderDetails($token);
            \Log::info("Order details: ", $orderDetails);
            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            \Log::error('PayPal success error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request)
    {
        \Log::info('PayPal Cancel Callback', $request->all());

        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled'
        ]);
    }
}
