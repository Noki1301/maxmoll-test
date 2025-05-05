<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{

	protected $fillable = ['order_id', 'product_id', 'count'];

	public $timestamps = false;

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
