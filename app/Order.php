<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{

	protected $fillable = [
		'customer',
		'created_at',
		'completed_at',
		'warehouse_id',
		'status',
	];

	public $timestamps = false; // Отключаем автоматическое управление временными метками

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function items()
	{
		return $this->hasMany(OrderItem::class);
	}
}
