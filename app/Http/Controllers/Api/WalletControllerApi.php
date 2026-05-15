<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletControllerApi extends Controller
{
    /**
     * Get user wallet info
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        return response()->json([
            'status' => 'success',
            'data' => [
                'wallet_number' => $wallet->wallet_number,
                'balance' => (float) $wallet->balance,
                'pending_balance' => (float) $wallet->pending_balance,
                'is_active' => (bool) $wallet->is_active,
                'has_pin' => !empty($wallet->pin),
            ]
        ]);
    }

    /**
     * Get wallet transaction history
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Top up simulation (Sandbox)
     */
    public function topup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        DB::beginTransaction();
        try {
            $wallet->credit(
                $request->amount,
                'topup',
                'Top Up Saldo via Sandbox'
            );
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Top up berhasil!',
                'new_balance' => (float) $wallet->balance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal top up: ' + $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify PIN (Helper for payments)
     */
    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:6']);
        
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        if ($wallet->pin === $request->pin) {
            return response()->json(['status' => 'success', 'message' => 'PIN Valid']);
        }

        return response()->json(['status' => 'error', 'message' => 'PIN Salah'], 401);
    }
    /**
     * Verify QR or VA code and process payment
     */
    public function verifyPayment(Request $request)
    {
        $request->validate(['code' => 'required|string|min:4']);

        $code = trim($request->code);
        $user = $request->user();

        // Find transaction by QR content or VA number
        $transaction = \App\Models\Transaction::where('buyer_id', $user->id)
            ->where('status', 'waiting_payment')
            ->where(function ($q) use ($code) {
                $q->where('meypay_qr_content', $code)
                  ->orWhere('meypay_va', $code);
            })
            ->first();

        if (!$transaction) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode tidak valid atau transaksi tidak ditemukan. Pastikan kode QR/VA sesuai dengan pesanan Anda.',
            ], 404);
        }

        // Delegate payment to TransactionController logic
        $request->merge(['_verified_transaction_id' => $transaction->id]);
        $txController = new \App\Http\Controllers\Api\TransactionControllerApi();
        return $txController->payWithWallet($request, $transaction->id);
    }
}
