<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Пользователи
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('loader_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Адреса
            $table->string('from_address');
            $table->string('to_address')->nullable();
            
            // Типы
            $table->string('vehicle_type');
            $table->enum('order_type', ['point_to_point', 'one_point'])->default('point_to_point');
            
            // Время и комментарий
            $table->string('desired_time')->nullable();
            $table->text('comment')->nullable();
            
            // Деньги
            $table->decimal('price', 10, 2)->default(500);
            $table->decimal('driver_price', 10, 2)->default(500);
            $table->decimal('loader_price', 10, 2)->default(0);
            
            // Грузчики
            $table->boolean('need_loaders')->default(false);
            $table->string('work_volume')->nullable();
            
            // Статус
            $table->enum('status', [
                'searching',                 // Ищем водителя
                'waiting_for_loader',        // Ищем грузчика
                'active',                    // В работе
                'completed_by_driver',       // Водитель завершил
                'completed_by_loader',       // Грузчик завершил
                'confirmed_by_customer',     // Заказчик подтвердил
                'cancelled'                  // Отменён
            ])->default('searching');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};