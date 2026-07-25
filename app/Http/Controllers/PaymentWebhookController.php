<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentTransaction;
use App\Models\Serial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    /**
     * Handle incoming payment webhooks from a gateway (e.g. SSLCommerz, bKash)
     */
    public function handle(Request $request, $gateway)
    {
        // Example payload: { "serial_id": "uuid...", "status": "VALID", "trx_id": "..." }
        $payload = $request->all();
        
        Log::info("Received {$gateway} webhook", $payload);

        if (!$this->verifySignature($request, $gateway)) {
            Log::warning("Invalid signature for {$gateway} webhook");
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $serialId = $request->input('serial_id');
        $status = $request->input('status'); // VALID, FAILED, etc.
        $trxId = $request->input('trx_id');

        if (!$serialId) {
            return response()->json(['error' => 'Missing serial_id'], 400);
        }

        DB::transaction(function () use ($gateway, $payload, $serialId, $status, $trxId) {
            $serial = Serial::lockForUpdate()->find($serialId);
            
            if ($serial) {
                // Record the transaction
                PaymentTransaction::create([
                    'serial_id' => $serial->id,
                    'gateway' => $gateway,
                    'webhook_payload' => json_encode($payload),
                    'verified_at' => $status === 'VALID' ? now() : null,
                ]);

                // Update serial status
                if ($status === 'VALID') {
                    $serial->update([
                        'payment_status' => 'paid',
                        'payment_reference' => $trxId,
                    ]);
                } else {
                    $serial->update([
                        'payment_status' => 'failed',
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Verify the webhook signature based on the gateway
     */
    private function verifySignature(Request $request, string $gateway): bool
    {
        // Placeholder for actual signature verification per gateway
        switch ($gateway) {
            case 'bkash':
                // e.g. check a custom header vs hash_hmac of payload
                $signature = $request->header('X-Signature');
                return $signature === hash_hmac('sha256', json_encode($request->all()), config('services.bkash.secret', 'dummy_secret'));
            
            case 'sslcommerz':
                // e.g. check verify_sign field
                return $request->input('verify_sign') === md5($request->input('trx_id') . config('services.sslcommerz.store_password', 'dummy_pass'));

            case 'nagad':
                // e.g. check a custom header or payload signature
                $nagadSignature = $request->header('X-Nagad-Signature');
                return !empty($nagadSignature); // placeholder
            
            default:
                // Unknown gateways shouldn't be trusted
                return false;
        }
    }
}
