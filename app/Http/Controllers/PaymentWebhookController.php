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
}
