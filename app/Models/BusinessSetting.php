<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_id', 'date_format', 'invoice_prefix', 'invoice_footer',
        'tax_enabled', 'default_tax', 'low_stock_alert', 'notification_preferences', 'language',
    ];

    protected function casts(): array
    {
        return [
            'tax_enabled' => 'boolean',
            'low_stock_alert' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }
}
