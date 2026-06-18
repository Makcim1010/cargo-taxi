<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_number')->nullable();           // номер прав
            $table->date('license_date_from')->nullable();          // выдано с
            $table->date('license_date_to')->nullable();            // действительно до
            $table->boolean('has_own_loaders')->default(false);     // есть ли свои грузчики
            $table->integer('loaders_count')->default(0);           // количество грузчиков
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};