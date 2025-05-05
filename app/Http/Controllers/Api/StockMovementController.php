<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\StockMovement;

class StockMovementController extends Controller
{
    /**
     * Получение истории движения товаров с фильтрами и пагинацией.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse']);

        // Фильтр по складу
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Фильтр по товару
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Фильтр по дате начала
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        // Фильтр по дате окончания
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Пагинация
        $movements = $query->orderByDesc('created_at')->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $movements
        ]);
    }
}
