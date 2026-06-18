<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->date('issued_at')->nullable();   // выдана с
            $table->date('expires_at')->nullable();  // действует до
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_category');
    }
};