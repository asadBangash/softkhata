<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'category_id', 'unit_id', 'name', 'sku', 'barcode',
        'purchase_price', 'selling_price', 'current_stock', 'minimum_stock',
        'description', 'image_path', 'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'current_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
        ];
    }
}
