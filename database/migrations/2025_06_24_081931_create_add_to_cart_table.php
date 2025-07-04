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
        Schema::create('add_to_cart', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('product_title')->nullable();
            $table->string('price')->nullable();
            $table->string('quantity')->nullable();
             $table->string('total_price')->nullable();
            $table->string('image')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
      
        
        // Foreign key constraints (optional but recommended)
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_to_cart');
    }
};
