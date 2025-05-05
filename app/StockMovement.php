<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'direction',     // 'in' yoki 'out'
        'description',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
