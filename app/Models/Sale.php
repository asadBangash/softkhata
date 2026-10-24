<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'party_id', 'sale_number', 'date', 'subtotal', 'discount', 'tax',
        'grand_total', 'amount_paid', 'balance', 'payment_status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
