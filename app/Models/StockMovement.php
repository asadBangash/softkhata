<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'product_id', 'type', 'quantity', 'reference_type',
        'reference_id', 'notes', 'created_by',
    ];
}
