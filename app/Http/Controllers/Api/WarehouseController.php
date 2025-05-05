<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Возвращает список складов.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $warehouses = Warehouse::all();

        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }
}
