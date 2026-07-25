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
                // Record the transaction idempotently
                PaymentTransaction::updateOrCreate(
                    [
                        'gateway' => $gateway,
                        'transaction_id' => $trxId,
                    ],
                    [
                        'serial_id' => $serial->id,
                        'webhook_payload' => json_encode($payload),
                        'verified_at' => $status === 'VALID' ? now() : null,
                    ]
                );

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
     * Verify the webhook signature using HMAC-SHA256.
     * Assumes payload has a 'signature' field.
     */
    private function verifySignature(Request $request, string $gateway): bool
    {
        $tenant = tenant();
        if (!$tenant || empty($tenant->payment_gateway_secret)) {
            return false;
        }

        $signature = $request->input('signature');
        if (empty($signature)) {
            return false;
        }

        // Standard verification: collect all fields except signature
        $payload = $request->except('signature');
        
        // Sort keys alphabetically to ensure consistent payload representation
        ksort($payload);
        $dataToSign = json_encode($payload);

        $expectedSignature = hash_hmac('sha256', $dataToSign, $tenant->payment_gateway_secret);

        return hash_equals($expectedSignature, (string) $signature);
    }
}
