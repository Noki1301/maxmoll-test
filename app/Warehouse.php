<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{

    protected $fillable = ['name'];

    /**
     * Ombordagi barcha stoklar.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Ombordagi barcha buyurtmalar.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
