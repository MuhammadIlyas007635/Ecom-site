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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_title');
            $table->text('description');
            $table->string('image');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->unsignedBigInteger('category_id'); // Make sure this matches categories.id
            $table->decimal('discount', 10, 2)->nullable();
            

            // Foreign Key
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
