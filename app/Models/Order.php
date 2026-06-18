<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'driver_id',
        'loader_id',
        'from_address',
        'to_address',
        'vehicle_type',
        'order_type',
        'desired_time',
        'comment',
        'price',
        'driver_price',
        'loader_price',
        'need_loaders',
        'work_volume',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function loader()
    {
        return $this->belongsTo(User::class, 'loader_id');
    }
}