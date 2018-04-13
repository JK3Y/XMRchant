<?php

namespace JK3Y\Xmrchant\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'account_id',
        'subaddress_id',
        'type',
        'amount',
        'fee',
        'height',
        'timestamp'
    ];
}
