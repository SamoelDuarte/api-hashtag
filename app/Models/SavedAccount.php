<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'type',
        'category',
        'access_token',
        'is_active',
        'additional_info',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_info' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
