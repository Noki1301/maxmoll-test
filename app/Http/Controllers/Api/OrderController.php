<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\OrderItem;
use App\Product;
use App\Stock;
use App\StockMovement;

class OrderController extends Controller
{
    /**
     * Получение списка заказов с фильтрами и пагинацией.
     */
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'warehouse']);

        // Фильтрация по статусу
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по складу
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Фильтрация по дате (от)
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        // Фильтрация по дате (до)
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        // Пагинация
        $orders = $query->orderByDesc('created_at')->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Создание нового заказа с вычитанием товаров со склада.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.count' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Создание самого заказа
            $order = Order::create([
                'customer' => $validated['customer'],
                'warehouse_id' => $validated['warehouse_id'],
                'created_at' => now(),
                'status' => 'active',
            ]);

            // Обработка каждой позиции
            foreach ($validated['items'] as $item) {
                // Проверка остатков
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->first();

                if (!$stock || $stock->stock < $item['count']) {
                    throw new \Exception("Недостаточно товара на складе для товара ID {$item['product_id']}");
                }

                // Вычитание со склада
                $stock->stock -= $item['count'];
                $stock->save();

                // Сохранение позиции заказа
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);

                // Логирование движения
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'quantity' => $item['count'],
                    'direction' => 'out',
                    'description' => 'Создан заказ: #' . $order->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан',
                'data' => $order->load('items.product')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Обновление активного заказа: позиции и клиент.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.count' => 'required|integer|min:1',
        ]);

        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Изменять можно только активные заказы'], 400);
        }

        DB::beginTransaction();

        try {
            // Возврат старых позиций на склад
            foreach ($order->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                $stock->stock += $oldItem->count;
                $stock->save();

                StockMovement::create([
                    'product_id' => $oldItem->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $oldItem->count,
                    'direction' => 'in',
                    'description' => 'Обновление заказа — возврат: #' . $order->id,
                ]);
            }

            // Удаление старых позиций
            $order->items()->delete();

            // Добавление новых позиций
            foreach ($validated['items'] as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                if (!$stock || $stock->stock < $item['count']) {
                    throw new \Exception("Недостаточно товара на складе для товара ID {$item['product_id']}");
                }

                $stock->stock -= $item['count'];
                $stock->save();

                $order->items()->create($item);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $item['count'],
                    'direction' => 'out',
                    'description' => 'Обновление заказа: #' . $order->id,
                ]);
            }

            $order->customer = $validated['customer'];
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно обновлён',
                'data' => $order->load('items.product')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Завершение активного заказа.
     */
    public function complete($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Только активные заказы можно завершить'], 400);
        }

        $order->status = 'completed';
        $order->completed_at = now();
        $order->save();

        return response()->json(['success' => true, 'message' => 'Заказ завершён']);
    }

    /**
     * Отмена активного заказа и возврат товаров на склад.
     */
    public function cancel($id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Отменять можно только активные заказы'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                $stock->stock += $item->count;
                $stock->save();

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $item->count,
                    'direction' => 'in',
                    'description' => 'Заказ отменён: #' . $order->id,
                ]);
            }

            $order->status = 'canceled';
            $order->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Заказ отменён']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Возобновление отменённого заказа (если хватает остатков).
     */
    public function resume($id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== 'canceled') {
            return response()->json(['success' => false, 'message' => 'Можно возобновить только отменённые заказы'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                if (!$stock || $stock->stock < $item->count) {
                    throw new \Exception("Недостаточно товара на складе для товара ID {$item->product_id}");
                }

                $stock->stock -= $item->count;
                $stock->save();

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $item->count,
                    'direction' => 'out',
                    'description' => 'Заказ возобновлён: #' . $order->id,
                ]);
            }

            $order->status = 'active';
            $order->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Заказ возобновлён']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
