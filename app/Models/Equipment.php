<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'link',
        'category',
        'price',
        'original_price',
        'discount_percentage',
        'last_price_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_price_checked_at' => 'datetime',
        ];
    }
}
