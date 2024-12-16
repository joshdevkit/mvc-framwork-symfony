<?php

use App\Core\Cli\Migration;
use App\Core\Cli\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function ($table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
