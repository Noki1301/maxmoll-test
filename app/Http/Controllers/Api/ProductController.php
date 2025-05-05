<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Product;
use App\Stock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Возвращает список всех товаров с остатками по каждому складу.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $products = Product::with(['stocks.warehouse'])->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stocks' => $product->stocks->map(function ($stock) {
                    return [
                        'warehouse' => $stock->warehouse->name,
                        'stock' => $stock->stock,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
