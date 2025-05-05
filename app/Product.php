<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{

    protected $fillable = ['name', 'price'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
