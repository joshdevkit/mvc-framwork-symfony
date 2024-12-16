<?php

use App\Core\Cli\Migration;
use App\Core\Cli\Schema;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('is_open_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
