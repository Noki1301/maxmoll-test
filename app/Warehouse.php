<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{

    protected $fillable = ['name'];

    /**
     * Все складские запасы.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Все заказы склада.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
