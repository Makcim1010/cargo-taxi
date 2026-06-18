<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'license_number',
        'license_date_from',
        'license_date_to',
        'has_own_loaders',
        'loaders_count'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'driver_category')
                    ->withPivot('issued_at', 'expires_at')
                    ->withTimestamps();
    }

    public function getCategoryNamesAttribute()
    {
        return $this->categories->pluck('name')->toArray();
    }
}