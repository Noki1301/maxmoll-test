<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = ['product_id', 'warehouse_id', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
