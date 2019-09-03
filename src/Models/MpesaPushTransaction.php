<?php

namespace Tumainimosha\MpesaPush\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MpesaPushTransaction.
 * @package Tumainimosha\MpesaPush\Models
 *
 * @property string reference
 * @property string customer_msisdn
 * @property double amount
 * @property string business_number
 * @property Carbon callback_received_at
 * @property string callback_status
 * @property string callback_description
 * @property string mpesa_transaction_id
 */
class MpesaPushTransaction extends Model
{
    protected $fillable = [
        'reference',
        'customer_msisdn',
        'amount',
        'business_number',
        'callback_received_at',
        'callback_status',
        'callback_description',
        'mpesa_transaction_id',
    ];

    protected $casts = [
        'reference' => 'string',
        'customer_msisdn' => 'string',
        'amount' => 'double',
        'business_number' => 'string',
        'callback_received_at' => 'datetime',
        'callback_status' => 'string',
        'callback_description' => 'string',
        'mpesa_transaction_id' => 'string',
    ];
}
