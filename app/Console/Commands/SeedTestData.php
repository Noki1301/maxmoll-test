<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Product;
use App\Warehouse;
use App\Stock;
use App\Order;
use App\OrderItem;
use App\StockMovement;
use Illuminate\Support\Facades\DB;

class SeedTestData extends Command
{
    // Название artisan-команды
    protected $signature = 'seed:test-data';

    // Описание команды
    protected $description = 'Создание тестовых складов, товаров и остатков';

    /**
     * Основной метод, который выполняет команду
     */
    public function handle()
    {
        $this->info('🔄 Очистка таблиц (отключение внешних ключей)...');

        // Отключаем внешние ключи для безопасного очистки таблиц
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Очередность очистки важна из-за внешних ключей
        Stock::truncate();
        OrderItem::truncate();
        Order::truncate();
        StockMovement::truncate();
        Product::truncate();
        Warehouse::truncate();

        // Включаем обратно внешние ключи
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('🏗 Создание 5 складов...');
        $warehouses = collect();
        foreach (range(1, 5) as $i) {
            $warehouses->push(Warehouse::create([
                'name' => "Склад №$i"
            ]));
        }

        $this->info('📦 Создание 10 товаров...');
        $products = collect();
        foreach (range(1, 10) as $i) {
            $products->push(Product::create([
                'name' => "Товар $i",
                'price' => rand(100, 1000),
            ]));
        }

        $this->info('📊 Распределение остатков товаров по складам...');
        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                Stock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock' => rand(5, 30),
                ]);
            }
        }

        $this->info('✅ Тестовые данные успешно созданы.');
        return 0;
    }
}
