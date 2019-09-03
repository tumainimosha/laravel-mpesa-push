<?php

namespace Tumainimosha\MpesaPush;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Tumainimosha\MpesaPush\Events\MpesaCallbackReceived;
use Tumainimosha\MpesaPush\Models\MpesaPushTransaction as Transaction;

class CallbackService
{
    public function getGenericResult($request)
    {
        $dataItems = $request->dataItem;

        logger('Mpesa Callback Received', $dataItems);

        $result = [];
        foreach ($dataItems as $dataItem) {
            $result[$dataItem->name] = $dataItem->value;
        }

        $reference = Arr::get($result, 'ThirdPartyReference');
        $status = Arr::get($result, 'TransactionStatus');
        $mpesaTxnId = Arr::get($result, 'TransID');

        try {
            /** @var Transaction $transaction */
            $transaction = Transaction::query()
                ->where('reference', $reference)
                ->firstOrFail();

            $transaction->callback_received_at = now();
            $transaction->callback_status = $status;
            $transaction->mpesa_transaction_id = $mpesaTxnId;
            $transaction->save();

            // Dispatch callback received event
            event(new MpesaCallbackReceived($transaction));
        } catch (ModelNotFoundException $e) {
            logger("Callback Error! Transaction with reference $reference not found!");

            return;
        }

        return;
    }
}
