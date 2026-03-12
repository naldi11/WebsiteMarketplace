<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;

class PaymentMethodControllerApi extends Controller
{
    /**
     * Get a list of active payment methods
     */
    public function index()
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $methods
        ]);
    }
}
