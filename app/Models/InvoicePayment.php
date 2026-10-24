<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id', 'amount', 'payment_method_id', 'date', 'reference', 'notes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
